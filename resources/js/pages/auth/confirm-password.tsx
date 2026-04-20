import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler } from 'react';

import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTranslations } from '@/hooks/use-translations';
import AuthLayout from '@/layouts/auth-layout';

export default function ConfirmPassword() {
	const { t } = useTranslations();
	const form = useForm<Required<{ password: string }>>({
		password: '',
	});
	const { data, setData, processing, errors } = form;
	const post = (...args: Parameters<typeof form.post>) => form.post(...args);
	const reset = (...args: Parameters<typeof form.reset>) => form.reset(...args);

	const submit: FormEventHandler = (e) => {
		e.preventDefault();

		post(route('password.confirm'), {
			onFinish: () => reset('password'),
		});
	};

	return (
		<AuthLayout
			title={t('auth.confirm_password.title')}
			description={t('auth.confirm_password.description')}
		>
			<Head title={t('auth.confirm_password.title')} />

			<form onSubmit={submit}>
				<div className="space-y-6">
					<div className="grid gap-2">
						<Label htmlFor="password">
							{t('auth.confirm_password.password_label')}
						</Label>
						<Input
							id="password"
							type="password"
							name="password"
							placeholder={t('auth.confirm_password.password_placeholder')}
							autoComplete="current-password"
							value={data.password}
							autoFocus
							onChange={(e) => setData('password', e.target.value)}
						/>

						<InputError message={errors.password} />
					</div>

					<div className="flex items-center">
						<Button className="w-full" disabled={processing}>
							{processing && <LoaderCircle className="h-4 w-4 animate-spin" />}
							{t('auth.confirm_password.submit_button')}
						</Button>
					</div>
				</div>
			</form>
		</AuthLayout>
	);
}
