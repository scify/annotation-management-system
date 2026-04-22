import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import React from 'react';

// Explicit interface — avoids AnchorHTMLAttributes conflicting with Inertia Link event types
interface TextLinkProps {
	href: string;
	children: React.ReactNode;
	className?: string;
	/** Force opening in a new tab. When omitted, inferred from the href (http/protocol-relative). */
	openInNewTab?: boolean;
	// MouseEventHandler<Element> is compatible with both <a> and Inertia's <Link> onClick types
	onClick?: React.MouseEventHandler;
	id?: string;
	'aria-label'?: string;
	rel?: string;
	target?: React.HTMLAttributeAnchorTarget;
	download?: boolean | string;
	tabIndex?: number;
}

export default function TextLink({
	href,
	children,
	className,
	openInNewTab,
	...props
}: TextLinkProps) {
	const isExternal = openInNewTab ?? (href.startsWith('http') || href.startsWith('//'));

	const classes = cn('text-primary underline-offset-4 hover:underline', className);

	if (isExternal) {
		return (
			<a href={href} className={classes} target="_blank" rel="noopener noreferrer" {...props}>
				{children}
			</a>
		);
	}

	return (
		<Link href={href} className={classes} {...props}>
			{children}
		</Link>
	);
}
