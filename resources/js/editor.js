import {
    Autoformat,
    BlockQuote,
    Bold,
    ClassicEditor,
    CodeBlock,
    Essentials,
    Heading,
    Italic,
    Link,
    List,
    Paragraph,
} from 'ckeditor5';
import 'ckeditor5/ckeditor5.css';

const editors = new Map();

export const initializeRichEditors = () => {
    document.querySelectorAll('[data-rich-editor]').forEach(async (element) => {
        if (element.dataset.editorState) {
            return;
        }

        element.dataset.editorState = 'initializing';

        try {
            const editor = await ClassicEditor.create({
                attachTo: element,
                initialData: element.value,
                licenseKey: import.meta.env.VITE_CKEDITOR_LICENSE_KEY || 'GPL',
                plugins: [
                    Autoformat,
                    BlockQuote,
                    Bold,
                    CodeBlock,
                    Essentials,
                    Heading,
                    Italic,
                    Link,
                    List,
                    Paragraph,
                ],
                toolbar: [
                    'undo',
                    'redo',
                    '|',
                    'heading',
                    '|',
                    'bold',
                    'italic',
                    'link',
                    '|',
                    'bulletedList',
                    'numberedList',
                    'blockQuote',
                    'codeBlock',
                ],
            });

            const componentElement = element.closest('[wire\\:id]');
            const component = componentElement
                ? window.Livewire.find(componentElement.getAttribute('wire:id'))
                : null;

            editor.editing.view.change((writer) => {
                writer.setAttribute('dir', element.dataset.direction, editor.editing.view.document.getRoot());
            });

            editor.model.document.on('change:data', () => {
                component?.$set(element.dataset.model, editor.getData(), false);
            });

            element.dataset.editorState = 'ready';
            editors.set(element, editor);
        } catch (error) {
            delete element.dataset.editorState;
            console.error('Unable to initialize the rich-text editor.', error);
        }
    });
};

const cleanupDetachedEditors = () => {
    editors.forEach((editor, element) => {
        if (document.contains(element)) {
            return;
        }

        editor.destroy();
        editors.delete(element);
    });
};

window.initializeRichEditors = initializeRichEditors;

document.addEventListener('livewire:init', initializeRichEditors);
document.addEventListener('livewire:navigated', initializeRichEditors);

new MutationObserver(() => {
    cleanupDetachedEditors();
    initializeRichEditors();
}).observe(document.documentElement, { childList: true, subtree: true });

initializeRichEditors();
