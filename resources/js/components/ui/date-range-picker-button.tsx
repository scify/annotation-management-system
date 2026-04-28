import { cn } from '@/lib/utils';
import { CalendarDate, getLocalTimeZone, today } from '@internationalized/date';
import { Calendar, ChevronLeft, ChevronRight } from 'lucide-react';
import {
    Button,
    CalendarCell,
    CalendarGrid,
    CalendarGridBody,
    CalendarGridHeader,
    CalendarHeaderCell,
    DateRangePicker,
    Dialog,
    Group,
    Heading,
    Popover,
    RangeCalendar,
} from 'react-aria-components';

export type DateRangeValue = { start: CalendarDate; end: CalendarDate };

interface DateRangePickerButtonProps {
    value: DateRangeValue | null;
    onChange: (value: DateRangeValue | null) => void;
    placeholder: string;
    'aria-label': string;
    className?: string;
}

export function formatDateRange(range: DateRangeValue | null): string | null {
    if (!range) return null;
    const fmt = (d: CalendarDate) =>
        d.toDate(getLocalTimeZone()).toLocaleDateString(undefined, {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    return `${fmt(range.start)} – ${fmt(range.end)}`;
}

export function DateRangePickerButton({
    value,
    onChange,
    placeholder,
    'aria-label': ariaLabel,
    className,
}: DateRangePickerButtonProps) {
    const formatted = formatDateRange(value);

    return (
        <DateRangePicker
            aria-label={ariaLabel}
            className={cn('hover:cursor-pointer', className)}
            value={value}
            onChange={(range) => onChange(range ? { start: range.start, end: range.end } : null)}
            minValue={today(getLocalTimeZone())}
        >
            <Group className="w-full">
                <Button className="focus-visible:ring-brand-blue-700/50 flex h-10 w-full items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 text-left text-sm focus-visible:ring-2 focus-visible:outline-none">
                    <Calendar className="size-4 shrink-0 text-slate-800" aria-hidden="true" />
                    {formatted ? (
                        <span className="flex-1 text-slate-800">{formatted}</span>
                    ) : (
                        <span className="text-muted-foreground flex-1">{placeholder}</span>
                    )}
                    <ChevronRight
                        className="size-4 shrink-0 text-slate-400 opacity-50"
                        aria-hidden="true"
                    />
                </Button>
            </Group>

            <Popover
                className={cn(
                    'z-50 mt-1 rounded-2xl border border-slate-200 bg-white p-4 shadow-md',
                    'data-[entering]:animate-in data-[entering]:fade-in-0 data-[entering]:zoom-in-95',
                    'data-[exiting]:animate-out data-[exiting]:fade-out-0 data-[exiting]:zoom-out-95',
                )}
            >
                <Dialog className="outline-none">
                    <RangeCalendar aria-label={ariaLabel} className="w-[294px]">
                        <header className="mb-3 flex items-center justify-between">
                            <Button
                                slot="previous"
                                aria-label="Previous month"
                                className="bg-brand-blue-50 hover:bg-brand-blue-100 focus-visible:ring-brand-blue-700/50 flex size-8 items-center justify-center rounded-lg text-slate-800 focus-visible:ring-2 focus-visible:outline-none"
                            >
                                <ChevronLeft className="size-4" aria-hidden="true" />
                            </Button>
                            <Heading className="text-base font-medium text-slate-800" />
                            <Button
                                slot="next"
                                aria-label="Next month"
                                className="bg-brand-blue-50 hover:bg-brand-blue-100 focus-visible:ring-brand-blue-700/50 flex size-8 items-center justify-center rounded-lg text-slate-800 focus-visible:ring-2 focus-visible:outline-none"
                            >
                                <ChevronRight className="size-4" aria-hidden="true" />
                            </Button>
                        </header>

                        <CalendarGrid>
                            <CalendarGridHeader>
                                {(day) => (
                                    <CalendarHeaderCell className="pb-1 text-center text-sm font-normal text-slate-400">
                                        {day}
                                    </CalendarHeaderCell>
                                )}
                            </CalendarGridHeader>
                            <CalendarGridBody>
                                {(date) => (
                                    <CalendarCell
                                        date={date}
                                        className={({
                                            isSelected,
                                            isSelectionStart,
                                            isSelectionEnd,
                                            isOutsideMonth,
                                            isDisabled,
                                        }) =>
                                            cn(
                                                'flex size-[42px] cursor-pointer items-center justify-center rounded-lg text-base font-medium outline-none',
                                                'hover:bg-brand-blue-100 focus-visible:ring-brand-blue-700/50 focus-visible:ring-2',
                                                isOutsideMonth && 'text-slate-400',
                                                !isOutsideMonth && !isSelected && 'text-slate-800',
                                                isSelected &&
                                                    !isSelectionStart &&
                                                    !isSelectionEnd &&
                                                    'bg-brand-blue-50 hover:bg-brand-blue-100 rounded-none text-slate-900',
                                                (isSelectionStart || isSelectionEnd) &&
                                                    'bg-brand-blue-700 hover:bg-brand-blue-800 text-white',
                                                isSelectionStart && 'rounded-l-lg rounded-r-none',
                                                isSelectionEnd && 'rounded-l-none rounded-r-lg',
                                                isDisabled && 'cursor-not-allowed opacity-40',
                                            )
                                        }
                                    />
                                )}
                            </CalendarGridBody>
                        </CalendarGrid>
                    </RangeCalendar>
                </Dialog>
            </Popover>
        </DateRangePicker>
    );
}
