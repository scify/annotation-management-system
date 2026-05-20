import { Head, useForm, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler, useCallback, useEffect, useRef, useState } from 'react';
import 'altcha';
import InputError from '@/components/input-error';
import { LocaleToggle } from '@/components/locale-toggle';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTranslations } from '@/hooks/use-translations';

type LoginForm = {
    username: string;
    password: string;
    remember: boolean;
    captcha?: string;
};

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
    token?: string;
    redirectTo?: string;
    skipCaptcha?: boolean;
}

export default function Login({
    status,
    canResetPassword,
    token,
    redirectTo,
    skipCaptcha = false,
}: Readonly<LoginProps>) {
    useEffect(() => {
        setMounted(true);
        import('altcha').catch(() => {});
    }, []);

    const form = useForm<Required<LoginForm>>({
        username: '',
        password: '',
        remember: false,
        captcha: '',
    });
    const { data, setData, processing, errors } = form;
    const post = (...args: Parameters<typeof form.post>) => form.post(...args);
    const reset = (...args: Parameters<typeof form.reset>) => form.reset(...args);

    const { errors: pageErrors } = usePage().props as {
        errors: { username?: string; login?: string; captcha?: string };
    };
    const { t } = useTranslations();
    const [altchaError, setAltchaError] = useState<string>('');
    const [mounted, setMounted] = useState(false);

    const handleAltchaStateChange = useCallback(
        (event: CustomEvent<{ payload: string; state: string }>) => {
            if (event.detail?.state === 'verified') {
                const captchaToken = event.detail?.payload || '';
                setData('captcha', captchaToken);
                if (captchaToken) setAltchaError('');
            }
        },
        [setData, setAltchaError]
    );

    const altchaRef = useRef<HTMLElement>(null);

    useEffect(() => {
        const node = altchaRef.current;
        if (!node) return;
        node.addEventListener('statechange', handleAltchaStateChange as EventListener);
        return () => {
            node.removeEventListener('statechange', handleAltchaStateChange as EventListener);
        };
    }, [handleAltchaStateChange]);

    useEffect(() => {
        if (token && redirectTo) {
            localStorage.setItem('auth_token', token);
            window.history.replaceState({}, document.title);
            window.location.replace(redirectTo);
        }
    }, [token, redirectTo]);

    useEffect(() => {
        // Only run on mount
        fetch('/api/v1/user/info', {
            credentials: 'same-origin',
            headers: { Accept: 'application/json' },
        })
            .then((res) => {
                if (res.ok) window.location.reload();
            })
            .catch(() => {});
    }, []);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (!data.captcha && !skipCaptcha) {
            setAltchaError(t('auth.login.captcha'));
            return;
        }
        post(window.location.pathname + window.location.search, {
            onFinish: () => reset('password'),
        });
    };

    return (
        <>
            <Head title="Log in" />
            <div className="bg-muted relative flex min-h-screen items-center justify-center p-4 sm:p-6">
                <div className="absolute top-4 right-4">
                    <LocaleToggle className="rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100" />
                </div>

                <div className="flex w-full max-w-6xl items-stretch gap-12 lg:gap-28">
                    {/* Left panel — brand card with gradient + texture */}
                    <div className="from-brand-blue-950 to-brand-blue-700 relative hidden min-h-[700px] w-[600px] shrink-0 overflow-hidden rounded-[30px] bg-gradient-to-b lg:flex">
                        <img
                            src="/images/login/bg-texture.jpg"
                            alt=""
                            aria-hidden="true"
                            className="pointer-events-none absolute inset-0 size-full object-cover opacity-40 mix-blend-multiply select-none"
                        />
                        <div className="absolute top-10 left-10 flex items-center gap-3">
                            <img
                                src="/images/logo-icon.svg"
                                alt=""
                                aria-hidden="true"
                                className="size-[72px]"
                            />
                            <img
                                src="/images/logo-text.svg"
                                alt="annotrAIn"
                                className="h-[38px] w-auto"
                            />
                        </div>
                    </div>

                    {/* Right panel — login form */}
                    <div className="mx-auto flex w-full max-w-md flex-col justify-center lg:mx-0 lg:max-w-[420px]">
                        <hgroup className="mb-8">
                            <h1 className="font-[family-name:var(--font-heading)] text-[30px] leading-[36px] font-light text-slate-800">
                                {t('auth.login.title')}
                            </h1>
                            <p className="mt-3 text-sm font-medium text-slate-500">
                                {t('auth.login.description')}
                            </p>
                        </hgroup>

                        <form className="flex flex-col gap-5" onSubmit={submit}>
                            {pageErrors.login && (
                                <div className="bg-destructive/15 rounded-md p-4">
                                    <div className="flex">
                                        <div className="flex-shrink-0">
                                            <svg
                                                className="text-destructive h-5 w-5"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    fillRule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                    clipRule="evenodd"
                                                />
                                            </svg>
                                        </div>
                                        <div className="ml-3">
                                            <p className="text-destructive text-sm">
                                                {pageErrors.login}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            )}

                            <div className="grid gap-2">
                                <Label htmlFor="username" className="font-semibold text-slate-800">
                                    {t('auth.login.username')}
                                </Label>
                                <Input
                                    id="username"
                                    type="text"
                                    required
                                    autoFocus
                                    autoComplete="username"
                                    value={data.username}
                                    onChange={(e) => setData('username', e.target.value)}
                                    placeholder={t('auth.login.username')}
                                    className="border-slate-200 bg-white"
                                />
                                <InputError message={pageErrors.username} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password" className="font-semibold text-slate-800">
                                    {t('auth.login.password')}
                                </Label>
                                <Input
                                    id="password"
                                    type="password"
                                    required
                                    autoComplete="current-password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder={t('auth.login.password')}
                                    className="border-slate-200 bg-white"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-2">
                                    <Checkbox
                                        id="remember"
                                        name="remember"
                                        checked={data.remember}
                                        onCheckedChange={(checked) => setData('remember', checked)}
                                    />
                                    <Label
                                        htmlFor="remember"
                                        className="text-sm font-medium text-slate-700"
                                    >
                                        {t('auth.login.remember')}
                                    </Label>
                                </div>
                                {canResetPassword && (
                                    <TextLink
                                        href={route('password.request')}
                                        openInNewTab={false}
                                        className="ml-0 text-sm font-semibold text-slate-800 underline"
                                    >
                                        {t('auth.login.forgot_password')}
                                    </TextLink>
                                )}
                            </div>

                            {!skipCaptcha && mounted && (
                                <div className="flex justify-start">
                                    <altcha-widget
                                        id="altcha-widget"
                                        hidelogo
                                        hidefooter
                                        challenge="/altcha-challenge"
                                        ref={altchaRef}
                                    />
                                </div>
                            )}
                            {pageErrors.captcha && <InputError message={pageErrors.captcha} />}
                            {altchaError && <InputError message={altchaError} />}

                            <Button
                                type="submit"
                                disabled={processing}
                                className="bg-brand-yellow-300 text-brand-blue-900 hover:bg-brand-yellow-400 active:bg-brand-yellow-400 mt-2 w-full py-4 font-semibold"
                            >
                                {processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
                                {t('auth.login.button')}
                            </Button>
                        </form>

                        {status && (
                            <p className="mt-4 text-center text-sm font-medium text-green-600">
                                {status}
                            </p>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
