import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { useTranslations } from '@/hooks/use-translations';
import { Database, Info } from 'lucide-react';

export interface DatasetInfo {
	name: string;
	totalInstances: number;
	/** When provided, shows the info banner and pre-fills From instance */
	previousEndInstance?: number;
}

interface SelectDatasetSubsetStepProps {
	dataset: DatasetInfo;
	fromInstance: number;
	toInstance: number;
	shuffle: boolean;
	onFromInstanceChange: (value: number) => void;
	onToInstanceChange: (value: number) => void;
	onShuffleChange: (value: boolean) => void;
}

export function SelectDatasetSubsetStep({
	dataset,
	fromInstance,
	toInstance,
	shuffle,
	onFromInstanceChange,
	onToInstanceChange,
	onShuffleChange,
}: SelectDatasetSubsetStepProps) {
	const { t, trans } = useTranslations();
	const instanceCount = Math.max(0, toInstance - fromInstance);

	return (
		<section aria-labelledby="step-heading" className="flex flex-col gap-5">
			<h2 id="step-heading" className="sr-only">
				{t('sub-projects.select_dataset.select_subset_heading')}
			</h2>

			<div className="flex gap-x-32">
				{/* Left — Project Dataset */}
				<div className="flex w-72 shrink-0 flex-col gap-3">
					<h3 className="text-xl font-medium text-slate-800">
						{t('sub-projects.select_dataset.project_dataset_heading')}
					</h3>

					<div className="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-slate-100 p-6">
						<Database className="size-12 text-slate-600" aria-hidden="true" />

						<div className="flex flex-col gap-0.5">
							<p className="text-xl font-bold text-slate-900">
								{t('sub-projects.select_dataset.dataset_label')}
							</p>
							<p className="text-xl text-slate-900">{dataset.name}</p>
						</div>

						<span className="bg-brand-blue-100 self-start rounded-lg px-2.5 py-1 text-sm font-medium text-slate-800">
							{t('sub-projects.select_dataset.total_instances')}{' '}
							{dataset.totalInstances.toLocaleString()}
						</span>

						<label className="flex cursor-pointer items-center gap-2">
							<Checkbox
								checked={shuffle}
								onCheckedChange={onShuffleChange}
								aria-label={t('sub-projects.select_dataset.shuffle_on')}
							/>
							<span className="text-sm font-medium text-slate-900">
								{t('sub-projects.select_dataset.shuffle_on')}
							</span>
						</label>
					</div>
				</div>

				{/* Right — Select Subset */}
				<div className="flex flex-1 flex-col gap-4">
					<h3 className="text-xl font-medium text-slate-800">
						{t('sub-projects.select_dataset.select_subset_heading')}
					</h3>

					{/* Info banner */}
					{dataset.previousEndInstance !== undefined && (
						<div className="border-brand-blue-300 bg-brand-blue-50 flex flex-col gap-3 rounded-md border p-4">
							<div className="flex items-start gap-2">
								<Info
									className="text-brand-blue-800 mt-0.5 size-6 shrink-0"
									aria-hidden="true"
								/>
								<p className="text-brand-blue-800 text-base font-medium">
									{trans('sub-projects.select_dataset.previous_ended_at', {
										name: dataset.name,
										instance: dataset.previousEndInstance,
									})}
								</p>
							</div>
							<div className="pl-8">
								<button
									type="button"
									className="text-sm font-semibold text-slate-800 underline"
									onClick={() =>
										onFromInstanceChange(dataset.previousEndInstance + 1)
									}
								>
									{trans('sub-projects.select_dataset.start_from', {
										instance: dataset.previousEndInstance + 1,
									})}
								</button>
							</div>
						</div>
					)}

					{/* From / To inputs */}
					<div className="flex gap-6">
						<div className="flex flex-1 flex-col gap-1.5">
							<label
								htmlFor="from-instance"
								className="px-2.5 text-sm font-semibold text-slate-800"
							>
								{t('sub-projects.select_dataset.from_instance')}
							</label>
							<Input
								id="from-instance"
								type="number"
								inputMode="numeric"
								min={1}
								value={fromInstance}
								onChange={(e) => onFromInstanceChange(Number(e.target.value))}
								className="h-10 bg-white px-2.5"
							/>
						</div>

						<div className="flex flex-1 flex-col gap-1.5">
							<label
								htmlFor="to-instance"
								className="px-2.5 text-sm font-semibold text-slate-800"
							>
								{t('sub-projects.select_dataset.to_instance')}
							</label>
							<Input
								id="to-instance"
								type="number"
								inputMode="numeric"
								min={fromInstance + 1}
								max={dataset.totalInstances}
								value={toInstance}
								onChange={(e) => onToInstanceChange(Number(e.target.value))}
								className="h-10 bg-white px-2.5"
							/>
						</div>
					</div>

					{/* Instance count banner */}
					<div className="bg-brand-blue-700 flex h-12 items-center justify-center rounded-2xl">
						<span className="text-lg font-medium text-white">
							{trans('sub-projects.select_dataset.instances_selected', {
								count: instanceCount.toLocaleString(),
							})}
						</span>
					</div>
				</div>
			</div>
		</section>
	);
}
