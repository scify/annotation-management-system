import { cn } from '@/lib/utils';

interface AppLogoIconProps {
    /**
     * Caller must set the lockup height (e.g. `h-[26px]`); both images
     * scale from it. Gap can be overridden via e.g. `gap-2`.
     */
    className?: string;
    /** Render only the icon, hiding the wordmark (e.g. collapsed sidebar). */
    iconOnly?: boolean;
}

export default function AppLogoIcon({ className, iconOnly = false }: AppLogoIconProps) {
    return (
        <span className={cn('flex items-center gap-1', className)}>
            <img
                src="/images/logo-icon.svg"
                alt=""
                aria-hidden="true"
                className="h-full w-auto shrink-0"
            />
            {!iconOnly && (
                <img
                    src="/images/logo-text.svg"
                    alt="annotrAIn"
                    className="h-[52%] w-auto shrink-0"
                />
            )}
        </span>
    );
}
