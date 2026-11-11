const base = { width: 18, height: 18, viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: 2, strokeLinecap: 'round', strokeLinejoin: 'round' };
const make = (d) => (props) => (
    <svg {...base} {...props}>{d}</svg>
);

export const EyeIcon = make(<><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" /><circle cx="12" cy="12" r="3" /></>);
export const PencilIcon = make(<><path d="M12 20h9" /><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z" /></>);
export const TrashIcon = make(<><path d="M3 6h18" /><path d="M8 6V4h8v2" /><path d="M19 6l-1 14H6L5 6" /></>);
export const DotsIcon = make(<><circle cx="12" cy="5" r="1.2" /><circle cx="12" cy="12" r="1.2" /><circle cx="12" cy="19" r="1.2" /></>);
export const PlusIcon = make(<><path d="M12 5v14M5 12h14" /></>);
export const SearchIcon = make(<><circle cx="11" cy="11" r="7" /><path d="M21 21l-4.3-4.3" /></>);
export const SparkIcon = make(<><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z" /></>);
export const DownloadIcon = make(<><path d="M12 3v12" /><path d="M7 10l5 5 5-5" /><path d="M5 21h14" /></>);
export const UploadIcon = make(<><path d="M12 15V3" /><path d="M7 8l5-5 5 5" /><path d="M5 21h14" /></>);
export const LoginIcon = make(<><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" /><path d="M10 17l5-5-5-5" /><path d="M15 12H3" /></>);
export const UserPlusIcon = make(<><circle cx="9" cy="8" r="4" /><path d="M2 21a7 7 0 0 1 14 0" /><path d="M19 8v6M16 11h6" /></>);
export const MenuIcon = make(<><path d="M3 6h18M3 12h18M3 18h18" /></>);
export const CloseIcon = make(<><path d="M6 6l12 12M18 6L6 18" /></>);
