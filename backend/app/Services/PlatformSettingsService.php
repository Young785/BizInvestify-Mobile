<?php

namespace App\Services;

use App\Models\Setting;

class PlatformSettingsService
{
    public const KEY_PLATFORM = 'platform_settings';
    public const KEY_PRICING = 'pricing_plans';
    public const KEY_LOCALIZATION = 'localization_config';

    public function __construct(private ExchangeRateService $exchangeRates)
    {
    }

    public function getPlatformSettings(): array
    {
        return Setting::get(self::KEY_PLATFORM, $this->defaultPlatformSettings());
    }

    public function savePlatformSettings(array $settings): void
    {
        Setting::updateOrCreate(
            ['key' => self::KEY_PLATFORM],
            [
                'value' => $settings,
                'type' => Setting::TYPE_JSON,
                'group' => 'platform',
                'is_public' => false,
            ]
        );
    }

    public function getPricingPlans(): array
    {
        $stored = Setting::get(self::KEY_PRICING, $this->defaultPricingPlans());

        return $this->mergePricingPlansWithDefaults($stored);
    }

    private function mergePricingPlansWithDefaults(array $stored): array
    {
        $defaults = collect($this->defaultPricingPlans())->keyBy('id');
        $localeFields = ['name', 'description', 'period', 'features', 'not_included', 'cta', 'free_label'];

        return collect($stored)->map(function ($plan) use ($defaults, $localeFields) {
            $default = $defaults->get($plan['id'] ?? '');
            if (!$default) {
                return $plan;
            }

            foreach ($localeFields as $field) {
                if (!isset($default[$field])) {
                    continue;
                }
                $plan[$field] = array_merge($default[$field], $plan[$field] ?? []);
            }

            return $plan;
        })->values()->all();
    }

    public function savePricingPlans(array $plans): void
    {
        Setting::updateOrCreate(
            ['key' => self::KEY_PRICING],
            [
                'value' => $plans,
                'type' => Setting::TYPE_JSON,
                'group' => 'pricing',
                'is_public' => true,
            ]
        );
    }

    public function getLocalizationConfig(): array
    {
        $stored = Setting::get(self::KEY_LOCALIZATION, $this->defaultLocalizationConfig());

        return $this->mergeLocalizationWithDefaults($stored);
    }

    private function mergeLocalizationWithDefaults(array $stored): array
    {
        $defaults = $this->defaultLocalizationConfig();
        $defaultCountries = collect($defaults['countries'])->keyBy('code');
        $defaultLanguages = collect($defaults['languages'])->keyBy('code');

        $languages = collect($stored['languages'] ?? [])
            ->keyBy('code')
            ->merge($defaultLanguages)
            ->values()
            ->all();

        $countries = collect($stored['countries'] ?? [])->map(function ($country) use ($defaultCountries) {
            $def = $defaultCountries->get($country['code'] ?? '');
            if (!$def) {
                return $country;
            }

            return array_merge($def, $country);
        })->values()->all();

        return array_merge($defaults, $stored, [
            'countries' => $countries,
            'languages' => $languages,
        ]);
    }

    public function saveLocalizationConfig(array $config): void
    {
        Setting::updateOrCreate(
            ['key' => self::KEY_LOCALIZATION],
            [
                'value' => $config,
                'type' => Setting::TYPE_JSON,
                'group' => 'localization',
                'is_public' => true,
            ]
        );
    }

    public function getPublicConfig(?string $country = null, ?string $currency = null, ?string $language = null): array
    {
        $localization = $this->getLocalizationConfig();
        $resolved = $this->resolveLocale($localization, $country, $currency, $language);

        return [
            'country' => $resolved['country'],
            'currency' => $resolved['currency'],
            'language' => $resolved['language'],
            'localization' => $localization,
            'pricing_plans' => $this->formatPlansForLocale($this->getPricingPlans(), $resolved),
            'exchange_rate' => $this->getExchangeRateMeta($resolved),
            'monetization' => [
                'commission_rate' => $this->getPlatformSettings()['commission_rate'] ?? 5.0,
                'investment_fee_rate' => $this->getPlatformSettings()['investment_fee_rate'] ?? 3.0,
                'featured_listing_min_daily_budget' => $this->getPlatformSettings()['featured_listing_min_daily_budget'] ?? 5.0,
            ],
        ];
    }

    private function getExchangeRateMeta(array $resolved): ?array
    {
        $currency = $resolved['currency'];
        if ($currency === 'USD') {
            return null;
        }

        $countryCode = $resolved['country']['code'] ?? 'US';

        return [
            'source_currency' => 'USD',
            'target_currency' => $currency,
            'rate' => $this->exchangeRates->getRate('USD', $currency, $countryCode),
            'provider' => 'wise',
        ];
    }

    public function resolveLocale(array $localization, ?string $country, ?string $currency, ?string $language): array
    {
        $countries = $localization['countries'] ?? [];
        $defaultCountry = $localization['default_country'] ?? 'US';

        $selectedCountry = collect($countries)->firstWhere('code', strtoupper($country ?? ''));
        if (!$selectedCountry) {
            $selectedCountry = collect($countries)->firstWhere('code', $defaultCountry)
                ?? ($countries[0] ?? ['code' => 'US', 'currency' => 'USD', 'language' => 'en', 'name' => 'United States']);
        }

        $resolvedCurrency = strtoupper($currency ?? $selectedCountry['currency'] ?? 'USD');
        $resolvedLanguage = strtolower($language ?? $selectedCountry['language'] ?? 'en');

        $currencyExists = collect($localization['currencies'] ?? [])->contains(fn ($c) => $c['code'] === $resolvedCurrency);
        if (!$currencyExists) {
            $resolvedCurrency = $selectedCountry['currency'] ?? 'USD';
        }

        $languageExists = collect($localization['languages'] ?? [])->contains(fn ($l) => $l['code'] === $resolvedLanguage);
        if (!$languageExists) {
            $resolvedLanguage = $selectedCountry['language'] ?? 'en';
        }

        return [
            'country' => $selectedCountry,
            'currency' => $resolvedCurrency,
            'language' => $resolvedLanguage,
        ];
    }

    public function formatPlansForLocale(array $plans, array $resolved): array
    {
        $lang = $resolved['language'];
        $currency = $resolved['currency'];
        $countryCode = $resolved['country']['code'] ?? 'US';
        $currencies = collect($this->getLocalizationConfig()['currencies'] ?? []);
        $symbol = $currencies->firstWhere('code', $currency)['symbol'] ?? $currency;

        return collect($plans)->map(function ($plan) use ($lang, $currency, $symbol, $countryCode) {
            $usdAmount = (float) ($plan['prices']['USD']['amount'] ?? 0);
            $isFree = $plan['is_free'] ?? ($usdAmount <= 0);

            if ($isFree) {
                $amount = 0;
            } elseif ($currency === 'USD') {
                $amount = $usdAmount;
            } else {
                $amount = $this->exchangeRates->convert($usdAmount, 'USD', $currency, $countryCode);
            }

            $displayPrice = $isFree
                ? ($this->translate($plan['free_label'] ?? ['en' => 'Free'], $lang))
                : $symbol . number_format($amount, $amount == floor($amount) ? 0 : 2);

            return [
                'id' => $plan['id'],
                'name' => $this->translate($plan['name'] ?? [], $lang),
                'description' => $this->translate($plan['description'] ?? [], $lang),
                'price' => $displayPrice,
                'amount' => $amount,
                'currency' => $currency,
                'is_free' => $isFree,
                'period' => $this->translate($plan['period'] ?? [], $lang),
                'features' => $this->translateArray($plan['features'] ?? [], $lang),
                'not_included' => $this->translateArray($plan['not_included'] ?? [], $lang),
                'cta' => $this->translate($plan['cta'] ?? [], $lang),
                'popular' => $plan['popular'] ?? false,
                'icon' => $plan['icon'] ?? 'zap',
            ];
        })->values()->all();
    }

    private function translate(array $values, string $lang): string
    {
        return $values[$lang] ?? $values['en'] ?? (is_string($values) ? $values : '');
    }

    private function translateArray(array $values, string $lang): array
    {
        if (isset($values[$lang]) && is_array($values[$lang])) {
            return $values[$lang];
        }
        if (isset($values['en']) && is_array($values['en'])) {
            return $values['en'];
        }
        return is_array($values) && !isset($values['en']) ? array_values($values) : [];
    }

    public function defaultPlatformSettings(): array
    {
        return [
            'site_name' => 'BizInvestify',
            'site_description' => 'Your trusted marketplace for products, businesses, and investments',
            'site_url' => 'https://bizinvestify.com',
            'contact_email' => 'contact@bizinvestify.com',
            'support_email' => 'support@bizinvestify.com',
            'currency' => 'USD',
            'commission_rate' => 5.0,
            'investment_fee_rate' => 3.0,
            'minimum_withdrawal' => 50.0,
            'featured_listing_min_daily_budget' => 5.0,
            'featured_listing_approval_required' => true,
            'payment_methods' => ['stripe', 'paystack', 'flutterwave', 'bank_transfer'],
            'max_login_attempts' => 5,
            'session_timeout' => 60,
            'require_2fa' => false,
            'require_kyc' => true,
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_username' => '',
            'smtp_password' => '',
            'email_from_name' => 'BizInvestify',
            'email_from_address' => 'noreply@bizinvestify.com',
            'email_notifications' => true,
            'push_notifications' => true,
            'admin_notifications' => true,
            'maintenance_mode' => false,
            'debug_mode' => false,
            'log_level' => 'info',
            'cache_duration' => 3600,
        ];
    }

    public function defaultLocalizationConfig(): array
    {
        return [
            'default_country' => 'US',
            'default_currency' => 'USD',
            'default_language' => 'en',
            'countries' => [
                ['code' => 'US', 'name' => 'United States', 'currency' => 'USD', 'language' => 'en'],
                ['code' => 'GB', 'name' => 'United Kingdom', 'currency' => 'GBP', 'language' => 'en'],
                ['code' => 'NG', 'name' => 'Nigeria', 'currency' => 'NGN', 'language' => 'en'],
                ['code' => 'CA', 'name' => 'Canada', 'currency' => 'USD', 'language' => 'en'],
                ['code' => 'DE', 'name' => 'Germany', 'currency' => 'EUR', 'language' => 'de'],
                ['code' => 'FR', 'name' => 'France', 'currency' => 'EUR', 'language' => 'fr'],
                ['code' => 'IN', 'name' => 'India', 'currency' => 'INR', 'language' => 'en'],
                ['code' => 'AE', 'name' => 'United Arab Emirates', 'currency' => 'AED', 'language' => 'ar'],
                ['code' => 'ZA', 'name' => 'South Africa', 'currency' => 'ZAR', 'language' => 'en'],
                ['code' => 'BR', 'name' => 'Brazil', 'currency' => 'BRL', 'language' => 'pt'],
                ['code' => 'MX', 'name' => 'Mexico', 'currency' => 'USD', 'language' => 'es'],
                ['code' => 'JP', 'name' => 'Japan', 'currency' => 'USD', 'language' => 'en'],
                ['code' => 'CN', 'name' => 'China', 'currency' => 'USD', 'language' => 'en'],
                ['code' => 'AU', 'name' => 'Australia', 'currency' => 'USD', 'language' => 'en'],
                ['code' => 'KE', 'name' => 'Kenya', 'currency' => 'USD', 'language' => 'en'],
                ['code' => 'GH', 'name' => 'Ghana', 'currency' => 'USD', 'language' => 'en'],
                ['code' => 'EG', 'name' => 'Egypt', 'currency' => 'USD', 'language' => 'ar'],
                ['code' => 'SA', 'name' => 'Saudi Arabia', 'currency' => 'AED', 'language' => 'ar'],
                ['code' => 'SG', 'name' => 'Singapore', 'currency' => 'USD', 'language' => 'en'],
                ['code' => 'PH', 'name' => 'Philippines', 'currency' => 'USD', 'language' => 'en'],
            ],
            'currencies' => [
                ['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar'],
                ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro'],
                ['code' => 'GBP', 'symbol' => '£', 'name' => 'British Pound'],
                ['code' => 'NGN', 'symbol' => '₦', 'name' => 'Nigerian Naira'],
                ['code' => 'INR', 'symbol' => '₹', 'name' => 'Indian Rupee'],
                ['code' => 'AED', 'symbol' => 'د.إ', 'name' => 'UAE Dirham'],
                ['code' => 'ZAR', 'symbol' => 'R', 'name' => 'South African Rand'],
                ['code' => 'BRL', 'symbol' => 'R$', 'name' => 'Brazilian Real'],
                ['code' => 'CAD', 'symbol' => 'C$', 'name' => 'Canadian Dollar'],
                ['code' => 'JPY', 'symbol' => '¥', 'name' => 'Japanese Yen'],
            ],
            'languages' => [
                ['code' => 'en', 'name' => 'English'],
                ['code' => 'fr', 'name' => 'Français'],
                ['code' => 'de', 'name' => 'Deutsch'],
                ['code' => 'pt', 'name' => 'Português'],
                ['code' => 'es', 'name' => 'Español'],
                ['code' => 'ar', 'name' => 'العربية'],
                ['code' => 'hi', 'name' => 'हिन्दी'],
            ],
        ];
    }

    public function defaultPricingPlans(): array
    {
        return [
            [
                'id' => 'starter',
                'icon' => 'zap',
                'is_free' => true,
                'popular' => false,
                'free_label' => ['en' => 'Free', 'fr' => 'Gratuit', 'de' => 'Kostenlos', 'pt' => 'Grátis', 'es' => 'Gratis', 'ar' => 'مجاني'],
                'name' => ['en' => 'Starter', 'fr' => 'Débutant', 'de' => 'Starter', 'pt' => 'Inicial', 'es' => 'Inicial', 'ar' => 'المبتدئ'],
                'description' => [
                    'en' => 'Perfect for entrepreneurs just getting started',
                    'fr' => 'Parfait pour les entrepreneurs qui débutent',
                    'de' => 'Ideal für Unternehmer am Anfang',
                    'pt' => 'Perfeito para empreendedores iniciantes',
                    'es' => 'Perfecto para emprendedores que recién comienzan',
                    'ar' => 'مثالي لرواد الأعمال الذين يبدأون للتو',
                ],
                'period' => ['en' => 'forever', 'fr' => 'pour toujours', 'de' => 'für immer', 'pt' => 'para sempre', 'es' => 'para siempre', 'ar' => 'للأبد'],
                'prices' => [
                    'USD' => ['amount' => 0],
                    'EUR' => ['amount' => 0],
                    'GBP' => ['amount' => 0],
                    'NGN' => ['amount' => 0],
                    'INR' => ['amount' => 0],
                    'AED' => ['amount' => 0],
                    'ZAR' => ['amount' => 0],
                    'BRL' => ['amount' => 0],
                ],
                'features' => [
                    'en' => ['Up to 5 product listings', 'Basic marketplace access', 'Community support', 'Standard security', 'Basic analytics', 'Email support'],
                    'fr' => ['Jusqu\'à 5 annonces', 'Accès basique au marketplace', 'Support communautaire', 'Sécurité standard', 'Analyses basiques', 'Support par email'],
                    'de' => ['Bis zu 5 Produktlisten', 'Basis-Marktplatzzugang', 'Community-Support', 'Standardsicherheit', 'Basis-Analysen', 'E-Mail-Support'],
                    'pt' => ['Até 5 listagens de produtos', 'Acesso básico ao marketplace', 'Suporte da comunidade', 'Segurança padrão', 'Análises básicas', 'Suporte por email'],
                    'es' => ['Hasta 5 listados de productos', 'Acceso básico al mercado', 'Soporte comunitario', 'Seguridad estándar', 'Análisis básicos', 'Soporte por email'],
                    'ar' => ['حتى 5 قوائم منتجات', 'وصول أساسي للسوق', 'دعم المجتمع', 'أمان قياسي', 'تحليلات أساسية', 'دعم بالبريد'],
                ],
                'not_included' => [
                    'en' => ['Investment features', 'Advanced analytics', 'Priority support', 'Custom branding'],
                    'fr' => ['Fonctionnalités d\'investissement', 'Analyses avancées', 'Support prioritaire', 'Image de marque personnalisée'],
                    'de' => ['Investment-Funktionen', 'Erweiterte Analysen', 'Prioritäts-Support', 'Individuelles Branding'],
                    'pt' => ['Recursos de investimento', 'Análises avançadas', 'Suporte prioritário', 'Marca personalizada'],
                    'es' => ['Funciones de inversión', 'Análisis avanzados', 'Soporte prioritario', 'Marca personalizada'],
                    'ar' => ['ميزات الاستثمار', 'تحليلات متقدمة', 'دعم أولوية', 'علامة تجارية مخصصة'],
                ],
                'cta' => ['en' => 'Get Started Free', 'fr' => 'Commencer gratuitement', 'de' => 'Kostenlos starten', 'pt' => 'Começar grátis', 'es' => 'Empezar gratis', 'ar' => 'ابدأ مجاناً'],
            ],
            [
                'id' => 'professional',
                'icon' => 'trending-up',
                'is_free' => false,
                'popular' => true,
                'name' => ['en' => 'Professional', 'fr' => 'Professionnel', 'de' => 'Professional', 'pt' => 'Profissional', 'es' => 'Profesional', 'ar' => 'احترافي'],
                'description' => [
                    'en' => 'For growing businesses ready to scale',
                    'fr' => 'Pour les entreprises en croissance prêtes à évoluer',
                    'de' => 'Für wachsende Unternehmen bereit zu skalieren',
                    'pt' => 'Para negócios em crescimento prontos para escalar',
                    'es' => 'Para negocios en crecimiento listos para escalar',
                    'ar' => 'للأعمال النامية الجاهزة للتوسع',
                ],
                'period' => ['en' => 'per month', 'fr' => 'par mois', 'de' => 'pro Monat', 'pt' => 'por mês', 'es' => 'por mes', 'ar' => 'شهرياً'],
                'prices' => [
                    'USD' => ['amount' => 29],
                    'EUR' => ['amount' => 27],
                    'GBP' => ['amount' => 23],
                    'NGN' => ['amount' => 45000],
                    'INR' => ['amount' => 2400],
                    'AED' => ['amount' => 107],
                    'ZAR' => ['amount' => 520],
                    'BRL' => ['amount' => 145],
                ],
                'features' => [
                    'en' => ['Unlimited product listings', 'Investment opportunities', 'Advanced search & filtering', 'Priority support', 'Advanced analytics', 'Custom branding', 'API access', 'Team collaboration'],
                    'fr' => ['Annonces illimitées', 'Opportunités d\'investissement', 'Recherche avancée', 'Support prioritaire', 'Analyses avancées', 'Image de marque', 'Accès API', 'Collaboration d\'équipe'],
                    'de' => ['Unbegrenzte Produktlisten', 'Investmentmöglichkeiten', 'Erweiterte Suche', 'Prioritäts-Support', 'Erweiterte Analysen', 'Individuelles Branding', 'API-Zugang', 'Team-Zusammenarbeit'],
                    'pt' => ['Listagens ilimitadas', 'Oportunidades de investimento', 'Busca avançada', 'Suporte prioritário', 'Análises avançadas', 'Marca personalizada', 'Acesso à API', 'Colaboração em equipe'],
                    'es' => ['Listados ilimitados', 'Oportunidades de inversión', 'Búsqueda avanzada', 'Soporte prioritario', 'Análisis avanzados', 'Marca personalizada', 'Acceso API', 'Colaboración en equipo'],
                    'ar' => ['قوائم منتجات غير محدودة', 'فرص استثمار', 'بحث وفلاتر متقدمة', 'دعم أولوية', 'تحليلات متقدمة', 'علامة تجارية مخصصة', 'وصول API', 'تعاون الفريق'],
                ],
                'not_included' => [
                    'en' => ['White-label solution', 'Custom integrations', 'Dedicated account manager'],
                    'fr' => ['Solution white-label', 'Intégrations personnalisées', 'Gestionnaire de compte dédié'],
                    'de' => ['White-Label-Lösung', 'Individuelle Integrationen', 'Dedizierter Account Manager'],
                    'pt' => ['Solução white-label', 'Integrações personalizadas', 'Gerente de conta dedicado'],
                    'es' => ['Solución white-label', 'Integraciones personalizadas', 'Gerente de cuenta dedicado'],
                    'ar' => ['حل white-label', 'تكاملات مخصصة', 'مدير حساب مخصص'],
                ],
                'cta' => ['en' => 'Start Professional', 'fr' => 'Démarrer Professionnel', 'de' => 'Professional starten', 'pt' => 'Iniciar Profissional', 'es' => 'Iniciar Profesional', 'ar' => 'ابدأ الاحترافي'],
            ],
            [
                'id' => 'enterprise',
                'icon' => 'crown',
                'is_free' => false,
                'popular' => false,
                'name' => ['en' => 'Enterprise', 'fr' => 'Entreprise', 'de' => 'Enterprise', 'pt' => 'Empresarial', 'es' => 'Empresarial', 'ar' => 'المؤسسات'],
                'description' => [
                    'en' => 'For large organizations with advanced needs',
                    'fr' => 'Pour les grandes organisations aux besoins avancés',
                    'de' => 'Für große Organisationen mit erweiterten Anforderungen',
                    'pt' => 'Para grandes organizações com necessidades avançadas',
                    'es' => 'Para grandes organizaciones con necesidades avanzadas',
                    'ar' => 'للمؤسسات الكبيرة ذات الاحتياجات المتقدمة',
                ],
                'period' => ['en' => 'per month', 'fr' => 'par mois', 'de' => 'pro Monat', 'pt' => 'por mês', 'es' => 'por mes', 'ar' => 'شهرياً'],
                'prices' => [
                    'USD' => ['amount' => 99],
                    'EUR' => ['amount' => 92],
                    'GBP' => ['amount' => 79],
                    'NGN' => ['amount' => 155000],
                    'INR' => ['amount' => 8200],
                    'AED' => ['amount' => 365],
                    'ZAR' => ['amount' => 1780],
                    'BRL' => ['amount' => 495],
                ],
                'features' => [
                    'en' => ['Everything in Professional', 'White-label solution', 'Custom integrations', 'Dedicated account manager', 'Advanced compliance', 'Custom reporting', 'Phone support', 'Training & onboarding'],
                    'fr' => ['Tout dans Professionnel', 'Solution white-label', 'Intégrations personnalisées', 'Gestionnaire dédié', 'Conformité avancée', 'Rapports personnalisés', 'Support téléphonique', 'Formation'],
                    'de' => ['Alles in Professional', 'White-Label-Lösung', 'Individuelle Integrationen', 'Dedizierter Manager', 'Erweiterte Compliance', 'Individuelle Berichte', 'Telefon-Support', 'Schulung'],
                    'pt' => ['Tudo no Profissional', 'Solução white-label', 'Integrações personalizadas', 'Gerente dedicado', 'Conformidade avançada', 'Relatórios personalizados', 'Suporte telefônico', 'Treinamento'],
                    'es' => ['Todo en Profesional', 'Solución white-label', 'Integraciones personalizadas', 'Gerente dedicado', 'Cumplimiento avanzado', 'Informes personalizados', 'Soporte telefónico', 'Capacitación'],
                    'ar' => ['كل ما في الاحترافي', 'حل white-label', 'تكاملات مخصصة', 'مدير حساب مخصص', 'امتثال متقدم', 'تقارير مخصصة', 'دعم هاتفي', 'تدريب وإعداد'],
                ],
                'not_included' => ['en' => [], 'fr' => [], 'de' => [], 'pt' => [], 'es' => [], 'ar' => []],
                'cta' => ['en' => 'Contact Sales', 'fr' => 'Contacter les ventes', 'de' => 'Vertrieb kontaktieren', 'pt' => 'Falar com vendas', 'es' => 'Contactar ventas', 'ar' => 'تواصل مع المبيعات'],
            ],
        ];
    }
}
