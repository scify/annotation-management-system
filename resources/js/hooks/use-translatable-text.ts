import { useTranslations } from '@/hooks/use-translations';

export function useTranslatableText(): (text: string | null) => string {
    const { trans } = useTranslations();

    return (text) => {
        if (!text) return '';
        try {
            const parsed: unknown = JSON.parse(text);
            if (
                typeof parsed === 'object' &&
                parsed !== null &&
                'key' in parsed &&
                typeof parsed.key === 'string'
            ) {
                const { key, params = {} } = parsed as {
                    key: string;
                    params?: Record<string, string | number>;
                };
                return trans(key, params);
            }
        } catch {
            // not JSON — render as-is
        }
        return text;
    };
}
