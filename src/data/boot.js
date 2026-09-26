/**
 * Server-provided boot data (AppPage inline script `window.lwSliderAdmin`).
 */
const boot = window.lwSliderAdmin || {};

export const VERSION = boot.version || '';
export const NAMESPACE = boot.namespace || 'lw-slider/v1';
export const DOCS_URL =
	boot.docsUrl || 'https://github.com/lwplugins/lw-slider#readme';
// Publishing needs publish_posts (the REST API checks it too).
export const CAN_PUBLISH = !! boot.canPublish;
// Defaults of a new slide and of a new slider's settings (Data\Defaults).
export const DEFAULT_SLIDE = boot.defaults?.slide || {};
export const DEFAULT_SETTINGS = boot.defaults?.settings || {};
export const LIMITS = boot.limits || {};
