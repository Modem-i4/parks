import Image from '@tiptap/extension-image'
import { VueNodeViewRenderer } from '@tiptap/vue-3'
import ResizableImageView from '@/Components/News/ResizableImageView.vue'

export default Image.extend({
  name: 'image',

  addAttributes() {
    return {
      ...this.parent?.(),
      width: {
        default: '100%',
        parseHTML: el => el.getAttribute('width') || el.style.width || '100%',
        renderHTML: attrs => ({ width: attrs.width || '100%' }),
      },
      align: {
        default: 'center',
        parseHTML: el => el.getAttribute('align') || el.getAttribute('data-align') || 'center',
        renderHTML: attrs => ({ align: attrs.align || 'center' }),
      },
    }
  },

  addNodeView() {
    return VueNodeViewRenderer(ResizableImageView)
  },
})
