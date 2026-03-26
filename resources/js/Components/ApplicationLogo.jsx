export default function ApplicationLogo(props) {
    return (
        <svg
            {...props}
            viewBox="0 0 64 64"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
        >
            <circle cx="22" cy="21" r="6" fill="currentColor" />
            <circle cx="42" cy="21" r="6" fill="currentColor" />
            <circle cx="32" cy="14" r="5" fill="currentColor" opacity="0.85" />
            <path
                d="M12 40c0-5.5 4.5-10 10-10s10 4.5 10 10v4H12v-4Z"
                fill="currentColor"
            />
            <path
                d="M32 40c0-5.5 4.5-10 10-10s10 4.5 10 10v4H32v-4Z"
                fill="currentColor"
            />
            <path
                d="M20 50c0-5.5 5.4-10 12-10s12 4.5 12 10v4H20v-4Z"
                fill="currentColor"
                opacity="0.85"
            />
        </svg>
    );
}
