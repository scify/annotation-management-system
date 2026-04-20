import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/use-translations';
import { type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { useCallback } from 'react';

interface LocaleToggleProps {
	className?: string;
}

export function LocaleToggle({ className }: LocaleToggleProps) {
	const { app } = usePage<SharedData>().props;
	const { t } = useTranslations();
	const nextLocale = app.locale === 'el' ? 'en' : 'el';

	const switchLocale = useCallback(() => {
		router.put(
			route('locale.update'),
			{ locale: nextLocale },
			{
				preserveScroll: true,
				preserveState: false,
			}
		);
	}, [nextLocale]);

	const label = app.locale === 'el' ? t('common.switch_to_english') : t('common.switch_to_greek');

	return (
		<Button
			variant="ghost"
			size="sm"
			className={className}
			onPress={switchLocale}
			aria-label={label}
		>
			{app.locale === 'el' ? 'GR' : 'EN'}
		</Button>
	);
}
