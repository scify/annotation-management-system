import { usePage } from '@inertiajs/react';
import type { TranslationMap } from '@/types';

export function useTranslations() {
	const { translations } = usePage().props as unknown as { translations: TranslationMap };

	const t = (key: string): string => {
		const keys = key.split('.');
		return keys.reduce(
			(acc: Record<string, unknown> | string, current) => {
				if (typeof acc === 'string') return acc;
				return (acc[current] ?? key) as Record<string, unknown> | string;
			},
			translations as unknown as Record<string, unknown>
		) as string;
	};

	const trans = (key: string, params: Record<string, string | number> = {}): string => {
		let result = t(key);
		for (const [k, v] of Object.entries(params)) {
			result = result.replace(`:${k}`, String(v));
		}
		return result;
	};

	return { t, trans };
}
