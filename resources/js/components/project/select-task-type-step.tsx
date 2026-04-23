import { Input } from '@/components/ui/input';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { ExternalLink, FileText, Search } from 'lucide-react';
import { useState } from 'react';

export interface TaskTypeCardData {
	id: number;
	name: string;
	description: string;
	/** Long-form guidelines shown on the second tab */
	guidelines: string;
	/** URL to the PDF guidelines document, opened in a new tab */
	guidelinesUrl?: string;
	tag: string;
}

export const MOCK_TASK_TYPES: TaskTypeCardData[] = [
	{
		id: 1,
		name: 'Audio Annotation – Recognise the mood',
		description:
			'Annotators will have to listen to audio clips and identify the emotional mood expressed in each recording.',
		guidelines:
			'In this task, annotators listen to a series of audio clips ranging from 10 to 60 seconds. For each clip, they must select the predominant emotional mood from a predefined list of options. Pay close attention to tone, tempo, and vocal quality. Multiple listens are encouraged before making a final selection. All clips have been cleared for research use.',
		guidelinesUrl: '/guidelines/audio-annotation-mood.pdf',
		tag: '#audio annotation',
	},
	{
		id: 2,
		name: 'Text annotation referring to english poets without confirmation',
		description:
			'Annotators will have to read two texts and answer to a simple question about the author.',
		guidelines:
			'Read each pair of texts carefully. For each pair, you will be asked to identify references to English poets. Mark every explicit and implicit reference you find. Provide a brief rationale for implicit references. Do not confirm your findings with external sources — your independent judgment is the basis of this annotation task.',
		guidelinesUrl: '/guidelines/text-annotation-poets.pdf',
		tag: '#text annotation',
	},
	{
		id: 3,
		name: 'Read Text multiple times – Recognise meaning changes',
		description:
			'Annotators will have to read two texts and answer to a simple question about meaning shifts.',
		guidelines:
			"You will be presented with the same text passage multiple times, each time with a different contextual framing. Read each version independently. After all readings, describe how your understanding of the text's meaning changed, if at all. Focus on subtle semantic shifts rather than surface-level changes.",
		guidelinesUrl: '/guidelines/text-annotation-meaning-changes.pdf',
		tag: '#text annotation',
	},
	{
		id: 4,
		name: 'Recognise the meaning of the word "Knowledge" in medieval textes',
		description:
			'Annotators will have to read two texts and answer to a simple question about medieval vocabulary.',
		guidelines:
			'For each provided excerpt from a medieval text, identify and annotate every occurrence of the word "knowledge" or its contextual equivalents. Consider the scholastic and theological context of each passage. Use the provided glossary of medieval Latin terms as a reference. Aim for consistency across all annotations.',
		guidelinesUrl: '/guidelines/medieval-knowledge-annotation.pdf',
		tag: '#text annotation',
	},
	{
		id: 5,
		name: 'Identify linguistic patterns in historical documents',
		description:
			'Annotators will have to read two texts and answer to a simple question about recurring linguistic structures.',
		guidelines:
			'Examine each historical document for recurring syntactic patterns, idiomatic expressions, and formulaic phrases. Tag each pattern using the provided schema. When uncertain, use the "uncertain" tag and add a note. Cross-referencing multiple documents is encouraged. Focus on patterns that appear at least twice across the document set.',
		guidelinesUrl: '/guidelines/linguistic-patterns.pdf',
		tag: '#text annotation',
	},
	{
		id: 6,
		name: 'Listen to audio and transcribe the text',
		description:
			'Annotators will have to listen to an audio recording and produce an accurate written transcription.',
		guidelines:
			'Listen to each audio recording in full before beginning your transcription. Transcribe verbatim, including false starts, filler words, and hesitations. Use the provided notation guide for overlapping speech and unintelligible segments. Accuracy is prioritised over speed. Each recording may be replayed as many times as needed.',
		guidelinesUrl: '/guidelines/audio-transcription.pdf',
		tag: '#audio annotation',
	},
];

interface TaskTypeCardProps {
	taskType: TaskTypeCardData;
	isSelected: boolean;
	onSelect: () => void;
}

function TaskTypeCard({ taskType, isSelected, onSelect }: Readonly<TaskTypeCardProps>) {
	const { t } = useTranslations();
	const [tab, setTab] = useState(0);

	return (
		// <div role="radio"> instead of <button> so that the dot <button>s inside are valid HTML
		<div
			role="radio"
			aria-checked={isSelected}
			tabIndex={0}
			onClick={onSelect}
			onKeyDown={(e) => {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					onSelect();
				}
			}}
			className={cn(
				'focus-visible:ring-brand-blue-700 flex h-[343px] w-full cursor-pointer flex-col rounded-2xl border border-slate-200 px-5 pt-5 pb-3 transition-colors outline-none focus-visible:ring-2',
				isSelected ? 'bg-brand-blue-50' : 'bg-white hover:bg-slate-50'
			)}
		>
			{/* Radio indicator */}
			<div className="flex w-full items-start">
				<span
					aria-hidden="true"
					className={cn(
						'flex size-4 shrink-0 items-center justify-center rounded-full border-2',
						isSelected ? 'border-brand-blue-700' : 'border-slate-300'
					)}
				>
					{isSelected && <span className="bg-brand-blue-700 size-2 rounded-full" />}
				</span>
			</div>

			{/* Tab 1 — icon + title + short description + guidelines button */}
			{tab === 0 && (
				<div className="flex min-h-0 flex-1 flex-col gap-3 pt-3">
					<FileText className="size-10 shrink-0 text-slate-400" aria-hidden="true" />
					<div className="flex flex-col gap-4">
						<p className="text-base leading-5 font-bold text-slate-800">
							{taskType.name}
						</p>
						<p className="line-clamp-3 text-sm leading-5 text-slate-500">
							{taskType.description}
						</p>
					</div>
					<div className="mt-auto pt-4">
						<a
							href={taskType.guidelinesUrl ?? '#'}
							target="_blank"
							rel="noopener noreferrer"
							onClick={(e) => e.stopPropagation()}
							className="bg-brand-blue-700 hover:bg-brand-blue-800 flex h-[30px] w-full items-center justify-center gap-1.5 rounded-lg transition-colors"
						>
							<span className="text-sm font-semibold text-white">
								{t('projects.select_task_type.view_guidelines')}
							</span>
							<ExternalLink className="size-3.5 text-white" aria-hidden="true" />
						</a>
					</div>
				</div>
			)}

			{/* Tab 2 — scrollable guidelines text, fills the same space as tab 1 */}
			{tab === 1 && (
				<div className="min-h-0 flex-1 pt-3">
					<p className="h-full overflow-y-auto text-sm leading-5 text-slate-600 [scrollbar-color:theme(colors.slate.300)_transparent] [scrollbar-width:thin]">
						{taskType.guidelines}
					</p>
				</div>
			)}

			{/* Footer — tab dot switchers + tag */}
			<div className="mt-3 flex shrink-0 items-center justify-between">
				<div
					role="tablist"
					aria-label={t('projects.select_task_type.card_pages_label')}
					className="flex gap-2"
				>
					{[0, 1].map((i) => (
						<button
							key={i}
							type="button"
							role="tab"
							aria-selected={tab === i}
							aria-label={`${t('projects.select_task_type.page_label')} ${i + 1}`}
							onClick={(e) => {
								e.stopPropagation();
								setTab(i);
							}}
							className={cn(
								'focus-visible:outline-brand-blue-700 size-3 rounded-full transition-colors hover:cursor-pointer focus-visible:outline-2',
								tab === i
									? 'bg-brand-blue-700'
									: 'border border-slate-300 bg-transparent'
							)}
						/>
					))}
				</div>
				<span className="text-xs text-slate-500">{taskType.tag}</span>
			</div>
		</div>
	);
}

interface SelectTaskTypeStepProps {
	/** Falls back to mock data when not provided */
	taskTypes?: TaskTypeCardData[];
	selectedId: number | null;
	onSelectionChange: (id: number) => void;
}

export function SelectTaskTypeStep({
	taskTypes,
	selectedId,
	onSelectionChange,
}: SelectTaskTypeStepProps) {
	const { t } = useTranslations();
	const displayTaskTypes = taskTypes ?? MOCK_TASK_TYPES;
	const [searchQuery, setSearchQuery] = useState('');

	const filtered = searchQuery
		? displayTaskTypes.filter(
				(tt) =>
					tt.tag.toLowerCase().includes(searchQuery.toLowerCase()) ||
					tt.name.toLowerCase().includes(searchQuery.toLowerCase())
			)
		: displayTaskTypes;

	return (
		<div className="flex flex-col gap-6">
			<div className="flex items-center justify-between">
				<h2 className="text-xl font-medium text-slate-800">
					{t('projects.select_task_type.heading')}
				</h2>
				<div className="relative w-72">
					<Search
						className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-slate-400"
						aria-hidden="true"
					/>
					<Input
						type="search"
						placeholder={t('projects.select_task_type.search_placeholder')}
						value={searchQuery}
						onChange={(e) => setSearchQuery(e.target.value)}
						className="pl-9"
						aria-label={t('projects.select_task_type.search_placeholder')}
					/>
				</div>
			</div>

			<div
				role="radiogroup"
				aria-label={t('projects.select_task_type.heading')}
				className="grid grid-cols-3 gap-6"
			>
				{filtered.map((tt) => (
					<TaskTypeCard
						key={tt.id}
						taskType={tt}
						isSelected={selectedId === tt.id}
						onSelect={() => onSelectionChange(tt.id)}
					/>
				))}
			</div>
		</div>
	);
}
