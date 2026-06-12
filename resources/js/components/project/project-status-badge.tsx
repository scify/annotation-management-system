import { Badge, badgeVariants } from '@/components/ui/badge';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import type { ProjectStatus } from '@/types';
import { type VariantProps } from 'class-variance-authority';
import { useEffect, useRef, useState } from 'react';

export type StatusVariant = Extract<
    NonNullable<VariantProps<typeof badgeVariants>['variant']>,
    'yellow' | 'lime' | 'slate' | 'pink'
>;

export const STATUS_VARIANT: Record<ProjectStatus, StatusVariant> = {
    in_progress: 'yellow',
    pending: 'slate',
    completed: 'lime',
};

/** How long the attention pulse stays armed after a status change (ms). */
const PULSE_DURATION = 1000;

interface ProjectStatusBadgeProps {
    status: ProjectStatus;
    className?: string;
}

/**
 * Status pill for a project. Plays a one-shot attention pulse and a colour morph
 * whenever the status changes in place (e.g. after an Inertia partial reload), so the
 * user notices the change without losing their place. Static on first render.
 */
export function ProjectStatusBadge({ status, className }: ProjectStatusBadgeProps) {
    const { t } = useTranslations();
    const previousStatus = useRef(status);
    const [justChanged, setJustChanged] = useState(false);

    useEffect(() => {
        if (previousStatus.current === status) {
            return;
        }
        previousStatus.current = status;
        setJustChanged(true);
        const timeout = window.setTimeout(() => setJustChanged(false), PULSE_DURATION);
        return () => window.clearTimeout(timeout);
    }, [status]);

    return (
        <Badge
            variant={STATUS_VARIANT[status]}
            className={cn(
                'motion-safe:transition-colors motion-safe:duration-500',
                justChanged && 'motion-safe:animate-status-pulse',
                className
            )}
        >
            {t(`projects.status.${status}`)}
        </Badge>
    );
}
