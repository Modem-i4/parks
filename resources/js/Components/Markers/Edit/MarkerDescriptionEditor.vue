<script setup>
import { onBeforeUnmount, watch } from 'vue'
import { EditorContent, useEditor } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'

import icBold from '@/assets/editor/bold.svg'
import icItalic from '@/assets/editor/italic.svg'
import icUnderline from '@/assets/editor/underline.svg'
import icLink from '@/assets/editor/link.svg'

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Додайте опис…' },
})

const emit = defineEmits(['update:modelValue'])

function editorContent(value) {
  if (!value || /<\/?[a-z][\s\S]*>/i.test(value)) return value || ''

  const escaped = value
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;')

  return escaped
    .split(/\n{2,}/)
    .map(paragraph => `<p>${paragraph.replaceAll('\n', '<br>')}</p>`)
    .join('')
}

const editor = useEditor({
  content: editorContent(props.modelValue),
  extensions: [
    StarterKit.configure({
      blockquote: false,
      bulletList: false,
      code: false,
      codeBlock: false,
      heading: false,
      horizontalRule: false,
      listItem: false,
      orderedList: false,
      strike: false,
      link: {
        autolink: true,
        linkOnPaste: true,
        openOnClick: false,
        HTMLAttributes: {
          rel: 'noopener noreferrer nofollow',
          target: '_blank',
        },
      },
    }),
    Placeholder.configure({ placeholder: props.placeholder }),
  ],
  editorProps: {
    attributes: {
      'aria-label': 'Опис маркера',
    },
  },
  onUpdate({ editor: currentEditor }) {
    emit('update:modelValue', currentEditor.isEmpty ? '' : currentEditor.getHTML())
  },
})

watch(() => props.modelValue, value => {
  if (!editor.value) return
  const content = editorContent(value)
  if (content !== editor.value.getHTML()) {
    editor.value.commands.setContent(content, false)
  }
})

onBeforeUnmount(() => editor.value?.destroy())

function setLink() {
  const previousUrl = editor.value?.getAttributes('link')?.href || ''
  const url = window.prompt('Вставте URL:', previousUrl)
  if (url === null) return
  if (url.trim() === '') {
    editor.value?.chain().focus().extendMarkRange('link').unsetLink().run()
    return
  }

  editor.value?.chain().focus().extendMarkRange('link').setLink({ href: url.trim() }).run()
}
</script>

<template>
  <div class="marker-description-editor">
    <div class="toolbar" role="toolbar" aria-label="Форматування опису">
      <button type="button" :class="{ active: editor?.isActive('bold') }" title="Жирний"
        @click="editor?.chain().focus().toggleBold().run()">
        <img :src="icBold" alt="" />
      </button>
      <button type="button" :class="{ active: editor?.isActive('italic') }" title="Курсив"
        @click="editor?.chain().focus().toggleItalic().run()">
        <img :src="icItalic" alt="" />
      </button>
      <button type="button" :class="{ active: editor?.isActive('underline') }" title="Підкреслений"
        @click="editor?.chain().focus().toggleUnderline().run()">
        <img :src="icUnderline" alt="" />
      </button>
      <button type="button" :class="{ active: editor?.isActive('link') }" title="Посилання" @click="setLink">
        <img :src="icLink" alt="" />
      </button>
    </div>

    <EditorContent :editor="editor" />
  </div>
</template>

<style scoped>
.marker-description-editor {
  overflow: hidden;
  border: 1px solid #d1d5db;
  border-radius: .375rem;
  background: white;
}
.toolbar {
  display: flex;
  gap: .25rem;
  padding: .375rem;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
}
.toolbar button {
  display: inline-flex;
  width: 2rem;
  height: 2rem;
  align-items: center;
  justify-content: center;
  border-radius: .375rem;
  color: #4b5563;
}
.toolbar button:hover { background: #e5e7eb; }
.toolbar button.active { background: #dcfce7; color: #15803d; }
.toolbar img { width: 1.125rem; height: 1.125rem; }
:deep(.ProseMirror) {
  min-height: 6rem;
  padding: .5rem;
  outline: none;
  color: #374151;
}
:deep(.ProseMirror p) { margin: .25rem 0; }
:deep(.ProseMirror a) { color: #2563eb; text-decoration: underline; }
:deep(.ProseMirror p.is-editor-empty:first-child::before) {
  content: attr(data-placeholder);
  float: left;
  height: 0;
  color: #9ca3af;
  pointer-events: none;
}
</style>
