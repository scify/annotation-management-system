import { ImgHTMLAttributes } from 'react';

export default function AppLogoIconMinimal(props: ImgHTMLAttributes<HTMLImageElement>) {
	return (
		<img
			{...props}
			src="/images/logo.svg"
			alt="App Logo"
			className={`w-auto ${props.className ?? ''}`}
		/>
	);
}
