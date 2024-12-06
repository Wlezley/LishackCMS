import { basicSetup } from "codemirror"
import { EditorState } from '@codemirror/state'
import { EditorView, keymap } from '@codemirror/view'
import { defaultKeymap, history, historyKeymap } from '@codemirror/commands'
import { defaultHighlightStyle, syntaxHighlighting } from '@codemirror/language'

// import { markdown, markdownKeymap } from '@codemirror/lang-markdown'
import { html } from "@codemirror/lang-html"

import { cobalt } from 'thememirror';

const state = EditorState.create({
	doc: '<p class="d-none">Test</p>',
  lineNumbers: true,
  styleActiveLine: true,
  matchBrackets: true,
	extensions: [
    basicSetup,
		cobalt,
    html(),
    history(),
    syntaxHighlighting(defaultHighlightStyle),
    keymap.of([defaultKeymap, historyKeymap]),
	]
});

const view = new EditorView({
	parent: document.querySelector('.codemirror-editor'),
	state
});
