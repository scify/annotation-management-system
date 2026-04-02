import { usePage } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import { type SharedData } from '@/types';

export function useFlashMessages() {
	const { errors, flash } = usePage<SharedData>().props;

	useEffect(() => {
		if (flash.success) {
			toast.success(flash.success);
		}
		if (flash.error) {
			toast.error(flash.error);
		}
		if (flash.warning) {
			toast.warning(flash.warning);
		}
		if (flash.info) {
			toast.info(flash.info);
		}
	}, [flash]);

	useEffect(() => {
		const errorMessages = Object.values(errors).filter(
			(value) => typeof value === 'string'
		) as string[];
		errorMessages.forEach((message) => toast.error(message));
	}, [errors]);
}
