/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */
import '../../vendor/masmerise/livewire-toaster/resources/js';
import './echo';

let editorModule;

const loadRichEditor = () => {
    if (! document.querySelector('[data-rich-editor]')) {
        return;
    }

    editorModule ??= import('./editor');
    editorModule.then(({ initializeRichEditors }) => initializeRichEditors());
};

document.addEventListener('DOMContentLoaded', loadRichEditor);
document.addEventListener('livewire:init', loadRichEditor);
document.addEventListener('livewire:navigated', loadRichEditor);

document.addEventListener('livewire:init', () => {
    const userId = document.querySelector('meta[name="authenticated-user-id"]')?.content;

    if (! userId || ! window.Echo) {
        return;
    }

    window.Echo.private(`users.${userId}`).notification((notification) => {
        document.dispatchEvent(new CustomEvent('toaster:received', {
            detail: { message: notification.message ?? 'New notification', type: 'info' },
        }));
        window.Livewire.dispatch('notification-received');
    });
});
