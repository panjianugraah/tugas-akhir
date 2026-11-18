import { Link } from '@inertiajs/react';

type Props = {
    label?: string;
    title: string;
    description: string;
    buttonLabel?: string;
    buttonHref?: string;
};

export default function PublicPageHeader({
    label = 'SMP Angkasa Adi Soemarmo',
    title,
    description,
    buttonLabel,
    buttonHref,
}: Props) {
    return (
        <section className="bg-gradient-to-br from-blue-700 to-blue-900 py-20 text-white">
            <div className="mx-auto max-w-5xl px-4 text-center">
                <p className="mb-2 text-sm font-semibold tracking-widest text-blue-200 uppercase">
                    {label}
                </p>

                <h1 className="mb-4 text-3xl font-bold md:text-5xl">
                    {title}
                </h1>

                <p
                    className={`mx-auto max-w-2xl text-blue-100 ${
                        buttonLabel && buttonHref ? 'mb-8' : ''
                    }`}
                >
                    {description}
                </p>

                {buttonLabel && buttonHref && (
                    <Link
                        href={buttonHref}
                        className="inline-block rounded-lg bg-yellow-400 px-8 py-3 font-semibold text-blue-900 shadow-lg transition hover:bg-yellow-300"
                    >
                        {buttonLabel}
                    </Link>
                )}
            </div>
        </section>
    );
}