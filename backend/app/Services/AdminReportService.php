<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Investment;
use App\Models\Kyc;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Support\DatabaseDateExpressions;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminReportService
{
    public function __construct(private PlatformSettingsService $platformSettings)
    {
    }

    public function filtersFromRequest(Request $request): array
    {
        $period = $request->query('period', 'monthly');
        if (! in_array($period, ['daily', 'weekly', 'monthly', 'yearly'], true)) {
            $period = 'monthly';
        }

        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        return [
            'period' => $period,
            'date_from' => $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : null,
            'date_to' => $dateTo ? Carbon::parse($dateTo)->endOfDay() : null,
        ];
    }

    public function overview(array $filters): array
    {
        $userQuery = $this->applyDateRange(User::query(), $filters, 'created_at');
        $transactionQuery = $this->applyDateRange(Transaction::query(), $filters, 'created_at');
        $completedQuery = (clone $transactionQuery)->where('status', Transaction::STATUS_COMPLETED);

        $newUsers = (clone $userQuery)->count();
        $totalTransactions = (clone $transactionQuery)->count();
        $totalRevenue = (float) (clone $completedQuery)->sum('amount');
        $totalCommission = $this->sumCommission(clone $completedQuery);

        $growthRate = $this->calculateUserGrowthRate($filters);

        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_verified', true)->count(),
            'new_users' => $newUsers,
            'new_users_this_month' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'total_transactions' => $totalTransactions,
            'total_revenue' => round($totalRevenue, 2),
            'total_commission' => round($totalCommission, 2),
            'growth_rate' => $growthRate,
            'active_listings' => Product::where('status', 'active')->count() + Business::where('status', 'active')->count(),
            'total_listings' => Product::count() + Business::count(),
            'completed_investments' => Investment::where('status', 'completed')->count(),
            'total_investments' => Investment::count(),
            'pending_kyc' => Kyc::whereIn('status', [Kyc::STATUS_PENDING, Kyc::STATUS_UNDER_REVIEW])->count(),
        ];
    }

    public function userReport(array $filters): array
    {
        $base = $this->applyDateRange(User::query(), $filters, 'created_at');
        $periodExpr = DatabaseDateExpressions::periodKey($filters['period'], 'created_at', 'period');

        $rows = (clone $base)
            ->select($periodExpr, DB::raw('count(*) as new_users'))
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn ($row) => $this->emptyRow((string) $row->period, [
                'new_users' => (int) $row->new_users,
            ]))
            ->values()
            ->all();

        return [
            'summary' => [
                'total_users' => User::count(),
                'active_users' => User::where('is_verified', true)->count(),
                'suspended_users' => User::where('kyc_status', 'rejected')->count(),
                'new_users' => (clone $base)->count(),
            ],
            'breakdown' => [
                'users_by_role' => User::select('role', DB::raw('count(*) as count'))->groupBy('role')->get(),
                'users_by_kyc_status' => User::select('kyc_status', DB::raw('count(*) as count'))->groupBy('kyc_status')->get(),
            ],
            'rows' => $rows,
        ];
    }

    public function transactionReport(array $filters): array
    {
        $base = $this->applyDateRange(Transaction::query(), $filters, 'created_at');
        $periodExpr = DatabaseDateExpressions::periodKey($filters['period'], 'created_at', 'period');

        $rows = (clone $base)
            ->select(
                $periodExpr,
                DB::raw('count(*) as total_transactions'),
                DB::raw('sum(amount) as total_revenue'),
                DB::raw('sum(coalesce(commission, 0)) as total_commission')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function ($row) {
                $revenue = (float) ($row->total_revenue ?? 0);
                $commission = (float) ($row->total_commission ?? 0);
                if ($commission <= 0 && $revenue > 0) {
                    $commission = $revenue * $this->defaultCommissionRate();
                }

                return $this->emptyRow((string) $row->period, [
                    'total_transactions' => (int) $row->total_transactions,
                    'total_revenue' => round($revenue, 2),
                    'total_commission' => round($commission, 2),
                ]);
            })
            ->values()
            ->all();

        $completed = (clone $base)->where('status', Transaction::STATUS_COMPLETED);

        return [
            'summary' => [
                'total_transactions' => (clone $base)->count(),
                'completed_transactions' => (clone $completed)->count(),
                'total_revenue' => round((float) (clone $completed)->sum('amount'), 2),
                'total_commission' => round($this->sumCommission(clone $completed), 2),
            ],
            'breakdown' => [
                'transactions_by_status' => (clone $base)->select('status', DB::raw('count(*) as count'))->groupBy('status')->get(),
            ],
            'rows' => $rows,
        ];
    }

    public function revenueReport(array $filters): array
    {
        $base = $this->applyDateRange(
            Transaction::query()->where('status', Transaction::STATUS_COMPLETED),
            $filters,
            'created_at'
        );
        $periodExpr = DatabaseDateExpressions::periodKey($filters['period'], 'created_at', 'period');

        $rows = (clone $base)
            ->select(
                $periodExpr,
                DB::raw('sum(amount) as total_revenue'),
                DB::raw('sum(coalesce(commission, 0)) as total_commission')
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function ($row) {
                $revenue = (float) ($row->total_revenue ?? 0);
                $commission = (float) ($row->total_commission ?? 0);
                if ($commission <= 0 && $revenue > 0) {
                    $commission = $revenue * $this->defaultCommissionRate();
                }

                return $this->emptyRow((string) $row->period, [
                    'total_revenue' => round($revenue, 2),
                    'total_commission' => round($commission, 2),
                ]);
            })
            ->values()
            ->all();

        return [
            'summary' => [
                'total_revenue' => round((float) (clone $base)->sum('amount'), 2),
                'total_commission' => round($this->sumCommission(clone $base), 2),
                'monthly_revenue' => round((float) Transaction::where('status', Transaction::STATUS_COMPLETED)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('amount'), 2),
                'yearly_revenue' => round((float) Transaction::where('status', Transaction::STATUS_COMPLETED)
                    ->whereYear('created_at', now()->year)
                    ->sum('amount'), 2),
            ],
            'rows' => $rows,
        ];
    }

    public function kycReport(array $filters): array
    {
        $submittedBase = $this->applyDateRange(Kyc::query(), $filters, 'submitted_at');
        $periodExpr = DatabaseDateExpressions::periodKey($filters['period'], 'submitted_at', 'period');

        $submittedRows = (clone $submittedBase)
            ->select($periodExpr, DB::raw('count(*) as count'))
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $approvedRows = $this->applyDateRange(
            Kyc::query()->where('status', Kyc::STATUS_APPROVED)->whereNotNull('reviewed_at'),
            $filters,
            'reviewed_at'
        )
            ->select(DatabaseDateExpressions::periodKey($filters['period'], 'reviewed_at', 'period'), DB::raw('count(*) as count'))
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $rejectedRows = $this->applyDateRange(
            Kyc::query()->where('status', Kyc::STATUS_REJECTED)->whereNotNull('reviewed_at'),
            $filters,
            'reviewed_at'
        )
            ->select(DatabaseDateExpressions::periodKey($filters['period'], 'reviewed_at', 'period'), DB::raw('count(*) as count'))
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->keyBy('period');

        $periods = collect($submittedRows->keys())
            ->merge($approvedRows->keys())
            ->merge($rejectedRows->keys())
            ->unique()
            ->sort()
            ->values();

        $rows = $periods->map(function ($period) use ($submittedRows, $approvedRows, $rejectedRows) {
            return $this->emptyRow((string) $period, [
                'kyc_submissions' => (int) ($submittedRows[$period]->count ?? 0),
                'kyc_approvals' => (int) ($approvedRows[$period]->count ?? 0),
                'kyc_rejections' => (int) ($rejectedRows[$period]->count ?? 0),
            ]);
        })->values()->all();

        return [
            'summary' => [
                'total_applications' => Kyc::count(),
                'pending_applications' => Kyc::whereIn('status', [Kyc::STATUS_PENDING, Kyc::STATUS_UNDER_REVIEW])->count(),
                'approved_applications' => Kyc::where('status', Kyc::STATUS_APPROVED)->count(),
                'rejected_applications' => Kyc::where('status', Kyc::STATUS_REJECTED)->count(),
            ],
            'rows' => $rows,
        ];
    }

    public function export(string $type, string $format, array $filters): array
    {
        if ($format !== 'csv') {
            throw new \InvalidArgumentException('Only CSV export is supported');
        }

        $report = match ($type) {
            'user' => $this->userReport($filters),
            'transaction' => $this->transactionReport($filters),
            'revenue' => $this->revenueReport($filters),
            'kyc' => $this->kycReport($filters),
            default => throw new \InvalidArgumentException('Invalid report type'),
        };

        $filename = sprintf('%s_report_%s.csv', $type, now()->format('Y-m-d_His'));
        $filepath = storage_path('app/public/exports/' . $filename);

        if (! is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');
        $headers = array_keys($report['rows'][0] ?? $this->emptyRow('sample'));
        fputcsv($file, $headers);

        foreach ($report['rows'] as $row) {
            fputcsv($file, array_map(fn ($key) => $row[$key] ?? '', $headers));
        }

        fclose($file);

        return [
            'download_url' => rtrim(config('app.url'), '/') . '/storage/exports/' . $filename,
            'filename' => $filename,
            'row_count' => count($report['rows']),
        ];
    }

    private function applyDateRange(Builder $query, array $filters, string $column): Builder
    {
        if ($filters['date_from']) {
            $query->where($column, '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $query->where($column, '<=', $filters['date_to']);
        }

        return $query;
    }

    private function sumCommission(Builder $query): float
    {
        $commission = (float) (clone $query)->sum('commission');
        if ($commission > 0) {
            return $commission;
        }

        $revenue = (float) (clone $query)->sum('amount');

        return $revenue * $this->defaultCommissionRate();
    }

    private function defaultCommissionRate(): float
    {
        $settings = $this->platformSettings->getPlatformSettings();

        return ((float) ($settings['commission_rate'] ?? 5)) / 100;
    }

    private function calculateUserGrowthRate(array $filters): float
    {
        $end = $filters['date_to'] ?? now();
        $start = $filters['date_from'] ?? now()->copy()->subMonth();
        $days = max(1, $start->diffInDays($end));
        $previousStart = $start->copy()->subDays($days);
        $previousEnd = $start->copy()->subSecond();

        $current = User::whereBetween('created_at', [$start, $end])->count();
        $previous = User::whereBetween('created_at', [$previousStart, $previousEnd])->count();

        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function emptyRow(string $period, array $overrides = []): array
    {
        return array_merge([
            'period' => $period,
            'total_users' => 0,
            'new_users' => 0,
            'total_transactions' => 0,
            'total_revenue' => 0.0,
            'total_commission' => 0.0,
            'active_listings' => 0,
            'completed_investments' => 0,
            'kyc_submissions' => 0,
            'kyc_approvals' => 0,
            'kyc_rejections' => 0,
        ], $overrides);
    }
}
