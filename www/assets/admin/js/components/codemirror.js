import { basicSetup } from "codemirror"
import { EditorState } from '@codemirror/state'
import { EditorView, keymap } from '@codemirror/view'
import { defaultKeymap, history, historyKeymap } from '@codemirror/commands'
import { defaultHighlightStyle, syntaxHighlighting } from '@codemirror/language'

// import { markdown, markdownKeymap } from '@codemirror/lang-markdown'
import { html } from "@codemirror/lang-html"
import { json } from "@codemirror/lang-json"
import { cobalt } from 'thememirror'

document.querySelectorAll('.codemirror-editor').forEach((editorEl) => {
  const wrapperEl = editorEl.closest('td')
  if (!wrapperEl) return;

  const inputEl = wrapperEl.querySelector('.codemirror-input');
  if (!inputEl) return;

  let lang;
  let langKeymap;
  switch(editorEl.dataset.lang) {
    case 'json':
      lang = json();
      langKeymap = defaultKeymap;
      break;

    default:
      lang = html();
      langKeymap = defaultKeymap;
      break;
  }

  const state = EditorState.create({
    doc: inputEl.value || '',
    lineNumbers: true,
    styleActiveLine: true,
    matchBrackets: true,
    extensions: [
      basicSetup,
      cobalt,
      lang,
      history(),
      syntaxHighlighting(defaultHighlightStyle),
      keymap.of([langKeymap, historyKeymap]),
      EditorView.updateListener.of((update) => {
        if (update.docChanged) {
          inputEl.value = update.state.doc.toString()
        }
      }),
    ]
  })

  const view = new EditorView({
    parent: editorEl,
    state
  })
})
