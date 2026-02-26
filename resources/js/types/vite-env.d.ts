/// <reference types="vite/client" />

interface ImportMetaEnv {
    readonly VITE_APP_NAME: string | undefined;
}

interface ImportMeta {
    readonly env: ImportMetaEnv;
}
