import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/hooks/use-translations';
import AuthLayout from '@/layouts/auth-layout';

export default function VerifyEmail({ status }: Readonly<{ status?: string }>) {
	const { t } = useTranslations();
	const { post, processing } = useForm({});

	const submit: FormEventHandler = (e) => {
		e.preventDefault();

		post(route('verification.send'));
	};

	return (
		<AuthLayout
			title={t('auth.verify_email.title')}
			description={t('auth.verify_email.description')}
		>
			<Head title="Email verification" />

			{status === 'verification-link-sent' && (
				<div className="mb-4 text-center text-sm font-medium text-green-600">
					{t('auth.verify_email.verification_sent')}
				</div>
			)}

			<form onSubmit={submit} className="space-y-6 text-center">
				<Button disabled={processing} variant="secondary">
					{processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
					{t('auth.verify_email.resend_button')}
				</Button>

				<TextLink
					href={route('logout')}
					openInNewTab={false}
					className="mx-auto block text-sm"
				>
					{t('auth.verify_email.logout_link')}
				</TextLink>
			</form>
		</AuthLayout>
	);
}
