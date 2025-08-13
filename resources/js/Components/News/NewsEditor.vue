<script setup>
import { ref, watch, onBeforeUnmount, onMounted, computed } from 'vue'

import { EditorContent, useEditor } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import TextAlign from '@tiptap/extension-text-align'
import Placeholder from '@tiptap/extension-placeholder'

import ResizableImage from '@/Helpers/Admin/ResizableImage'

// ====== ICONS ======
import icUndo from '@/assets/editor/undo.svg'
import icRedo from '@/assets/editor/redo.svg'
import icBold from '@/assets/editor/bold.svg'
import icItalic from '@/assets/editor/italic.svg'
import icStrike from '@/assets/editor/strike.svg'
import icUnderline from '@/assets/editor/underline.svg'
import icLink from '@/assets/editor/link.svg'
import icQuote from '@/assets/editor/blockquote.svg'
import icAlignLeft from '@/assets/editor/align-left.svg'
import icAlignCenter from '@/assets/editor/align-center.svg'
import icAlignRight from '@/assets/editor/align-right.svg'
import icAlignJustify from '@/assets/editor/align-justify.svg'
import icImage from '@/assets/editor/image.svg'

import PostMediaPickerModal from '@/Components/Media/PostMediaPickerModal.vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Напишіть новину…' },
  readonly: { type: Boolean, default: false },
})
const emit = defineEmits(['update:modelValue'])

const extensions = [
  StarterKit.configure({
    history: true,
    heading: { levels: [1, 2, 3, 4] },
    codeBlock: false,
    link: {
      autolink: true,
      linkOnPaste: true,
      // openOnClick: true,
    }
  }),
  ResizableImage.configure({ inline: false, allowBase64: true }),
  TextAlign.configure({ types: ['heading', 'paragraph'] }),
  Placeholder.configure({ placeholder: props.placeholder }),
]

const editor = useEditor({
  content: props.modelValue || '',
  editable: !props.readonly,
  extensions,
  onUpdate({ editor }) {
    emit('update:modelValue', editor.getHTML())
  },
})

watch(() => props.modelValue, (val) => {
  if (!editor.value) return
  if (val !== editor.value.getHTML()) {
    editor.value.commands.setContent(val || '', false)
  }
})

onBeforeUnmount(() => editor.value?.destroy())

// ===== Commands =====
function undo()  { editor.value?.chain().focus().undo().run() }
function redo()  { editor.value?.chain().focus().redo().run() }

function setParagraph() { editor.value?.chain().focus().setParagraph().run() }
function setHeading(lvl) { editor.value?.chain().focus().toggleHeading({ level: lvl }).run() }

function toggleMark(cmd) {
  const ch = editor.value?.chain().focus(); if (!ch) return
  if (cmd === 'bold') ch.toggleBold().run()
  if (cmd === 'italic') ch.toggleItalic().run()
  if (cmd === 'strike') ch.toggleStrike().run()
  if (cmd === 'underline') ch.toggleUnderline().run()
}

function setLink() {
  const prev = editor.value?.getAttributes('link')?.href || ''
  const url = window.prompt('Вставте URL:', prev)
  if (url === null) return
  if (url === '') return editor.value?.chain().focus().unsetLink().run()
  editor.value?.chain().focus().extendMarkRange('link').setLink({ href: url }).run()
}
function unsetLink() { editor.value?.chain().focus().unsetLink().run() }

function insertQuote()   { editor.value?.chain().focus().toggleBlockquote().run() }

// Align helpers
function setAlign(where) { editor.value?.chain().focus().setTextAlign(where).run() }
const currentAlignIcon = computed(() => {
  if (!editor.value) return icAlignLeft
  if (editor.value.isActive({ textAlign: 'center' })) return icAlignCenter
  if (editor.value.isActive({ textAlign: 'right' }))  return icAlignRight
  if (editor.value.isActive({ textAlign: 'justify' }))return icAlignJustify
  return icAlignLeft
})

// Headings label
const currentHeadingLabel = computed(() => {
  if (!editor.value) return 'H1'
  if (editor.value.isActive('heading', { level: 1 })) return 'H1'
  if (editor.value.isActive('heading', { level: 2 })) return 'H2'
  if (editor.value.isActive('heading', { level: 3 })) return 'H3'
  if (editor.value.isActive('heading', { level: 4 })) return 'H4'
  return 'H1'
})

// ===== Dropdowns + click outside (for Head/Align) =====
const openHead = ref(false)
const openAlign = ref(false)
const dropdownEls = []

function registerDrop(el) {
  if (el && !dropdownEls.includes(el)) dropdownEls.push(el)
}
function closeAll() {
  openHead.value = openAlign.value = false
}
function onDocClick(e) {
  const inside = dropdownEls.some(el => el && el.contains(e.target))
  if (!inside) closeAll()
}
onMounted(() => document.addEventListener('click', onDocClick))
onBeforeUnmount(() => document.removeEventListener('click', onDocClick))

// ===== PostMediaPickerModal wiring =====
const showPostPicker = ref(false)
function openPostPicker() { showPostPicker.value = true }
function closePostPicker() { showPostPicker.value = false }
function onPostPickerSaved(payload) {
  const url = payload?.file_path
  if (url) editor.value?.chain().focus().setImage({ src: url }).run()
  closePostPicker()
}
</script>

<template>
  <div class="editor-root">
    <!-- Toolbar -->
    <div class="toolbar">
      <!-- Undo / Redo -->
      <button class="btn" @click="undo" :disabled="!editor?.can().undo()" title="Undo">
        <img :src="icUndo" alt="" />
      </button>
      <button class="btn" @click="redo" :disabled="!editor?.can().redo()" title="Redo">
        <img :src="icRedo" alt="" />
      </button>

      <span class="sep"></span>

      <!-- Headings -->
      <div class="dd" :ref="registerDrop">
        <button class="btn" :class="{ active: editor?.isActive('heading') }"
          @click.stop="(openHead = !openHead, openAlign=false)">
          <span class="lbl">{{ currentHeadingLabel }}</span>
          <span class="car">▾</span>
        </button>
        <div v-if="openHead" class="menu">
          <button class="mi" :class="{sel: editor?.isActive('heading', {level:1})}" @click="setHeading(1)">H1</button>
          <button class="mi" :class="{sel: editor?.isActive('heading', {level:2})}" @click="setHeading(2)">H2</button>
          <button class="mi" :class="{sel: editor?.isActive('heading', {level:3})}" @click="setHeading(3)">H3</button>
          <button class="mi" :class="{sel: editor?.isActive('heading', {level:4})}" @click="setHeading(4)">H4</button>
          <button class="mi" :class="{sel: editor?.isActive('paragraph')}" @click="setParagraph">Параграф</button>
        </div>
      </div>

      <!-- Align -->
      <div class="dd" :ref="registerDrop">
        <button class="btn" @click.stop="(openAlign = !openAlign, openHead=false)">
          <img :src="currentAlignIcon" alt="" />
          <span class="car">▾</span>
        </button>
        <div v-if="openAlign" class="menu">
          <button class="mi" :class="{ sel: editor?.isActive({ textAlign:'left' }) }"   @click="setAlign('left')">
            <img :src="icAlignLeft" alt="" class="mi-ic" /> Left
          </button>
          <button class="mi" :class="{ sel: editor?.isActive({ textAlign:'center' }) }" @click="setAlign('center')">
            <img :src="icAlignCenter" alt="" class="mi-ic" /> Center
          </button>
          <button class="mi" :class="{ sel: editor?.isActive({ textAlign:'right' }) }"  @click="setAlign('right')">
            <img :src="icAlignRight" alt="" class="mi-ic" /> Right
          </button>
          <button class="mi" :class="{ sel: editor?.isActive({ textAlign:'justify' }) }" @click="setAlign('justify')">
            <img :src="icAlignJustify" alt="" class="mi-ic" /> Justify
          </button>
        </div>
      </div>

      <!-- Link -->
      <button class="btn" :class="{active: editor?.isActive('link')}" @click="setLink" title="Link">
        <img :src="icLink" alt="" />
      </button>

      <span class="sep"></span>

      <!-- Marks -->
      <button class="btn" :class="{active: editor?.isActive('bold')}" @click="toggleMark('bold')" title="Bold">
        <img :src="icBold" alt="" />
      </button>
      <button class="btn" :class="{active: editor?.isActive('italic')}" @click="toggleMark('italic')" title="Italic">
        <img :src="icItalic" alt="" />
      </button>
      <button class="btn" :class="{active: editor?.isActive('strike')}" @click="toggleMark('strike')" title="Strike">
        <img :src="icStrike" alt="" />
      </button>
      <button class="btn" :class="{active: editor?.isActive('underline')}" @click="toggleMark('underline')" title="Underline">
        <img :src="icUnderline" alt="" />
      </button>

      <!-- Quote / Divider -->
      <button class="btn" @click="insertQuote" title="Quote">
        <img :src="icQuote" alt="" />
      </button>

      <span class="sep"></span>

      <!-- Image -->
      <button class="btn" title="Додати зображення" @click="openPostPicker">
        <img :src="icImage" alt="Image" />
      </button>
    </div>

    <!-- Editor -->
    <div class="editor-wrap">
      <EditorContent :editor="editor" />
    </div>

    <!-- PostMediaPickerModal -->
    <PostMediaPickerModal
      v-if="showPostPicker"
      type="image"
      :onClose="closePostPicker"
      @saved="onPostPickerSaved"
    />
  </div>
</template>
<style scoped>
:deep(.ProseMirror) {
  outline: none;
}
.editor-root { display:grid; gap:.5rem; }
.toolbar {
  display:flex; align-items:center; justify-content: center; gap:.25rem;
  flex-wrap: wrap;
  background: linear-gradient(90deg, #f8fafc, #fff0);
  padding:.35rem; border-radius:.75rem; border:1px solid #e5e7eb;
}
.editor-root-wrap { border:1px solid #e5e7eb; border-radius:.75rem; background:#fff; padding:.75rem; min-height:280px; }

.btn {
  display:inline-flex; align-items:center; gap:.35rem;
  padding:.4rem .55rem; border-radius:.6rem; user-select:none;
  border:1px solid transparent; background:#fff0; color:#374151;
}
.btn:hover { background:#f3f4f6; }
.btn:disabled { opacity:.45; pointer-events:none; }
.btn.active { background:#ede9fe; color:#6d28d9; font-weight:600; }
.btn img { width:18px; height:18px; display:block; }
.lbl { font-size:.95rem; }
.car { margin-left:.15rem; opacity:.7 }
.sep { width:1px; height:18px; background:#e5e7eb; margin:0 .25rem; }

.dd { position:relative; }
.menu {
  position:absolute; top:100%; left:0; z-index:30;
  background:#fff; border:1px solid #e5e7eb; border-radius:.6rem;
  box-shadow:0 10px 30px rgba(0,0,0,.06);
  min-width:180px; padding:.35rem; margin-top:.35rem;
}
.mi {
  display:flex; align-items:center; gap:.5rem;
  width:100%; text-align:left; padding:.45rem .6rem; border-radius:.45rem;
  font-size:.95rem; color:#111827;
}
.mi:hover { background:#f3f4f6; }
.mi.sel { background:#ede9fe; color:#6d28d9; font-weight:600; }
.mi-ic { width:16px; height:16px; display:block; }
</style>