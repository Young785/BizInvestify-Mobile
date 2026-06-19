<?php

namespace App\Services;

use App\Models\Kyc;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class KycService
{
    public function formatForUser(?Kyc $kyc): ?array
    {
        if (! $kyc) {
            return null;
        }

        $score = $kyc->verification_score ?? $kyc->calculateVerificationScore();

        return [
            'id' => $kyc->id,
            'user_id' => $kyc->user_id,
            'document_type' => $kyc->document_type,
            'document_number' => $kyc->document_number,
            'status' => $kyc->status,
            'submitted_at' => $kyc->submitted_at,
            'reviewed_at' => $kyc->reviewed_at,
            'rejection_reason' => $kyc->rejection_reason,
            'reviewer_notes' => $kyc->notes,
            'verification_score' => $score,
            'can_resubmit' => $kyc->isRejected(),
            'document_urls' => $kyc->getDocumentUrls(),
            'address_line_1' => $kyc->address_line_1,
            'city' => $kyc->city,
            'state' => $kyc->state,
            'country' => $kyc->country,
            'nationality' => $kyc->nationality,
        ];
    }

    public function formatForAdminList(Kyc $kyc): array
    {
        $user = $kyc->user;
        $score = $kyc->verification_score ?? $kyc->calculateVerificationScore();

        return [
            'id' => (string) $kyc->id,
            'user_id' => (string) $kyc->user_id,
            'first_name' => $user?->first_name ?? '',
            'last_name' => $user?->last_name ?? '',
            'email' => $user?->email ?? '',
            'nationality' => $kyc->nationality ?? $user?->country ?? 'N/A',
            'status' => $kyc->status,
            'verification_score' => round((float) $score, 1),
            'submitted_at' => $kyc->submitted_at,
            'document_type' => $kyc->document_type,
            'document_number' => $kyc->document_number,
        ];
    }

    public function formatForAdmin(Kyc $kyc): array
    {
        $user = $kyc->user;
        $documents = $this->buildDocumentsList($kyc);

        return array_merge($this->formatForAdminList($kyc), [
            'date_of_birth' => $kyc->date_of_birth?->toDateString() ?? $user?->date_of_birth,
            'phone_number' => $user?->phone ?? $user?->phone_number,
            'address_line_1' => $kyc->address_line_1 ?? $user?->address,
            'address_line_2' => $kyc->address_line_2,
            'city' => $kyc->city ?? $user?->city,
            'state' => $kyc->state ?? $user?->state,
            'postal_code' => $kyc->postal_code ?? $user?->postal_code,
            'country' => $kyc->country ?? $user?->country,
            'occupation' => $kyc->occupation,
            'source_of_income' => $kyc->source_of_income,
            'rejection_reason' => $kyc->rejection_reason,
            'reviewed_at' => $kyc->reviewed_at,
            'documents' => $documents,
            'document_urls' => $kyc->getDocumentUrls(),
        ]);
    }

    public function buildDocumentsList(Kyc $kyc): array
    {
        $items = [
            ['id' => 'document_front', 'document_type' => 'document_front', 'path' => $kyc->document_front_path],
            ['id' => 'document_back', 'document_type' => 'document_back', 'path' => $kyc->document_back_path],
            ['id' => 'selfie', 'document_type' => 'selfie', 'path' => $kyc->selfie_path],
            ['id' => 'address_proof', 'document_type' => 'address_proof', 'path' => $kyc->address_proof_path],
        ];

        $documents = [];
        foreach ($items as $item) {
            if (! $item['path']) {
                continue;
            }

            $documents[] = [
                'id' => $item['id'],
                'document_type' => $item['document_type'],
                'document_path' => $item['path'],
                'document_number' => $item['id'] === 'document_front' ? $kyc->document_number : null,
                'status' => $kyc->status,
                'url' => Storage::disk('public')->url($item['path']),
            ];
        }

        return $documents;
    }

    public function submit(User $user, array $data, array $files): Kyc
    {
        $existing = Kyc::where('user_id', $user->id)->first();

        if ($existing && ! $existing->isRejected()) {
            throw new \InvalidArgumentException('KYC application already exists');
        }

        $paths = $this->storeFiles($user, $files, $existing);

        $payload = [
            'document_type' => $data['document_type'],
            'document_number' => $data['document_number'],
            'document_front_path' => $paths['document_front'],
            'document_back_path' => $paths['document_back'],
            'selfie_path' => $paths['selfie'],
            'address_proof_path' => $paths['proof_of_address'],
            'status' => Kyc::STATUS_PENDING,
            'submitted_at' => now(),
            'reviewed_at' => null,
            'reviewed_by' => null,
            'rejection_reason' => null,
            'notes' => null,
            'address_line_1' => $data['address_line_1'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? null,
            'nationality' => $data['nationality'] ?? null,
        ];

        if ($existing) {
            $this->deleteStoredFiles($existing);
            $existing->update($payload);
            $kyc = $existing->fresh();
        } else {
            $kyc = Kyc::create(array_merge($payload, ['user_id' => $user->id]));
        }

        $score = $kyc->calculateVerificationScore();
        $kyc->update(['verification_score' => $score]);

        $user->update([
            'kyc_status' => 'pending',
            'kyc_rejected_at' => null,
            'kyc_rejection_reason' => null,
        ]);

        return $kyc->fresh();
    }

    private function storeFiles(User $user, array $files, ?Kyc $existing): array
    {
        $stored = [];
        $map = [
            'document_front' => 'kyc/documents',
            'document_back' => 'kyc/documents',
            'selfie' => 'kyc/selfies',
            'proof_of_address' => 'kyc/address',
        ];

        foreach ($map as $field => $directory) {
            /** @var UploadedFile|null $file */
            $file = $files[$field] ?? null;
            if ($file) {
                $stored[$field] = $file->store($directory.'/'.$user->id, 'public');
            } elseif ($existing) {
                $pathField = $field === 'proof_of_address' ? 'address_proof_path' : $field.'_path';
                $stored[$field] = $existing->{$pathField};
            }
        }

        return $stored;
    }

    private function deleteStoredFiles(Kyc $kyc): void
    {
        foreach ([
            $kyc->document_front_path,
            $kyc->document_back_path,
            $kyc->selfie_path,
            $kyc->address_proof_path,
        ] as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    public function getDocumentUrl(string $path): ?string
    {
        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
