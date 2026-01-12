<style>
:root {
  /* Colors */
  --lexxy-color-ink: oklch(20% 0 0);
  --lexxy-color-ink-medium: oklch(40% 0 0);
  --lexxy-color-ink-light: oklch(60% 0 0);
  --lexxy-color-ink-lighter: oklch(85% 0 0);
  --lexxy-color-ink-lightest: oklch(96% 0 0);
  --lexxy-color-ink-inverted: white;

  --lexxy-color-accent-dark: oklch(57% 0.19 260);
  --lexxy-color-accent-medium: oklch(75% 0.196 258);
  --lexxy-color-accent-light: oklch(88% 0.026 254);
  --lexxy-color-accent-lightest: oklch(92% 0.026 254);

  --lexxy-color-red: oklch(60% 0.15 27);
  --lexxy-color-green: oklch(60% 0.15 145);
  --lexxy-color-blue: oklch(66% 0.196 258);
  --lexxy-color-purple: oklch(60% 0.15 305);

  --lexxy-color-code-token-att: #d73a49;
  --lexxy-color-code-token-comment: #6a737d;
  --lexxy-color-code-token-function: #6f42c1;
  --lexxy-color-code-token-operator: #d73a49;
  --lexxy-color-code-token-property: #005cc5;
  --lexxy-color-code-token-punctuation: #24292e;
  --lexxy-color-code-token-selector: #22863a;
  --lexxy-color-code-token-variable: #e36209;

  --lexxy-color-canvas: var(--lexxy-color-ink-inverted);
  --lexxy-color-text: var(--lexxy-color-ink);
  --lexxy-color-text-subtle: var(--lexxy-color-ink-medium);
  --lexxy-color-link: var(--lexxy-color-accent-dark);
  --lexxy-color-selected: var(--lexxy-color-accent-lightest);
  --lexxy-color-selected-hover: var(--lexxy-color-accent-light);
  --lexxy-color-selected-dark: var(--lexxy-color-blue);
  --lexxy-color-code-bg: var(--lexxy-color-ink-lightest);

  /* Text color highlights */
  --highlight-1: rgb(136, 118, 38);
  --highlight-2: rgb(185, 94, 6);
  --highlight-3: rgb(207, 0, 0);
  --highlight-4: rgb(216, 28, 170);
  --highlight-5: rgb(144, 19, 254);
  --highlight-6: rgb(5, 98, 185);
  --highlight-7: rgb(17, 138, 15);
  --highlight-8: rgb(148, 82, 22);
  --highlight-9: rgb(102, 102, 102);

  --highlight-bg-1: rgba(229, 223, 6, 0.3);
  --highlight-bg-2: rgba(255, 185, 87, 0.3);
  --highlight-bg-3: rgba(255, 118, 118, 0.3);
  --highlight-bg-4: rgba(248, 137, 216, 0.3);
  --highlight-bg-5: rgba(190, 165, 255, 0.3);
  --highlight-bg-6: rgba(124, 192, 252, 0.3);
  --highlight-bg-7: rgba(140, 255, 129, 0.3);
  --highlight-bg-8: rgba(221, 170, 123, 0.3);
  --highlight-bg-9: rgba(200, 200, 200, 0.3);

  /* Tables */
  --lexxy-color-table-header-bg: var(--lexxy-color-ink-lightest);
  --lexxy-color-table-cell-border: var(--lexxy-color-ink-lighter);
  --lexxy-color-table-cell-selected: var(--lexxy-color-selected);
  --lexxy-color-table-cell-selected-border: highlight;
  --lexxy-color-table-cell-selected-bg: highlight;

  /* Typography */
  --lexxy-font-base: system-ui, sans-serif;
  --lexxy-font-mono: ui-monospace, "Menlo", "Monaco", Consolas, monospace;
  --lexxy-text-small: 0.875rem;
  --lexxy-content-margin: 1rem;

  /* Focus ring */
  --lexxy-focus-ring-color: var(--lexxy-color-accent-dark);
  --lexxy-focus-ring-offset: 0;
  --lexxy-focus-ring-size: 2px;

  /* Misc */
  --lexxy-radius: 0.5ch;
  --lexxy-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  --lexxy-z-popup: 1000;
}

/* Text styles
/* -------------------------------------------------------------------------- */

:where(.lexxy-content) {
  color: var(--lexxy-color-ink);

  h1, h2, h3, h4, h5, h6 {
    display: block;
    font-weight: bold;
    hyphens: auto;
    margin-block: 0 var(--lexxy-content-margin);
    overflow-wrap: break-word;
    text-wrap: balance;
  }

  h1 { font-size: 2rem; }
  h2 { font-size: 1.5rem; }
  h3 { font-size: 1.25rem; }
  h4 { font-size: 1rem; }
  h5 { font-size: 0.875rem; }
  h6 { font-size: 0.75rem; }

  p,
  ul,
  ol,
  dl,
  blockquote,
  figure,
  .attachment {
    margin-block: 0 var(--lexxy-content-margin);

    &:not(lexxy-editor &) {
      overflow-wrap: break-word;
      text-wrap: pretty;
    }
  }

  .lexxy-content__italic {
    font-style: italic;
  }

  .lexxy-content__bold {
    font-weight: bold;
  }

  .lexxy-content__strikethrough {
    text-decoration: line-through;
  }

  .lexxy-content__underline {
    text-decoration: underline;
  }

  mark,
  .lexxy-content__highlight {
    background-color: transparent;
    color: inherit;
  }

  blockquote {
    border-inline-start: 0.25em solid var(--lexxy-color-ink-lighter);
    font-style: italic;
    margin: var(--lexxy-content-margin) 0;
    padding: 0.5lh 2ch;

    p:last-child {
      margin-block-end: 0;
    }
  }

  p:empty {
    display: none;
  }

  a {
    color: var(--lexxy-color-link);
  }

  img,
  video,
  embed,
  object {
    inline-size: auto;
    margin-inline: auto;
    max-block-size: 32rem;
    object-fit: contain;

    a:has(&) {
      display: inline-block;
    }
  }

  code, pre {
    background-color: var(--lexxy-color-ink-lightest);
    border-radius: var(--lexxy-radius);
    color: var(--lexxy-color-ink);
    font-family: var(--lexxy-font-mono);
    font-size: 0.9em;
    padding: 0.25ch 0.5ch;

    &:is(pre),
    &[data-language] {
      border-radius: var(--lexxy-radius);
      display: block;
      hyphens: none;
      margin-block: 0 var(--lexxy-content-margin);
      overflow-x: auto;
      padding: 1ch;
      tab-size: 2;
      text-wrap: nowrap;
      white-space: pre;
      word-break: break-word;
    }
  }

  li.lexxy-nested-listitem {
    list-style-type: none;

    ol, ul {
      margin: 0;
    }
  }

  > :last-child {
    margin-block-end: 0;
  }

  /* Keywords and attributes */
  .code-token__attr,
  .token.attr-name,
  .token.atrule,
  .token.attr,
  .token.keyword {
    color: var(--lexxy-color-code-token-att);
  }

  /* Constants, booleans, numbers, properties, tags */
  .code-token__property,
  .token.boolean,
  .token.constant,
  .token.number,
  .token.property,
  .token.symbol,
  .token.tag {
    color: var(--lexxy-color-code-token-property);
  }

  /* Strings, selectors, and built-in constructs */
  .code-token__selector,
  .token.attr-value,
  .token.builtin,
  .token.char,
  .token.inserted,
  .token.line,
  .token.selector,
  .token.string {
    color: var(--lexxy-color-code-token-selector);
  }

  /* Comments and meta information */
  .code-token__comment,
  .token.cdata,
  .token.comment,
  .token.doctype,
  .token.prolog {
    color: var(--lexxy-color-code-token-comment);
    font-style: italic;
  }

  /* Operators and symbolic entities */
  .code-token__operator,
  .token.deleted,
  .token.entity,
  .token.operator,
  .token.url,
  code[data-language="diff"] .code-token__operator + .code-token__selector {
    color: var(--lexxy-color-code-token-operator);
  }

  /* Functions and class names */
  .code-token__function,
  .token.class,
  .token.class-name,
  .token.function {
    color: var(--lexxy-color-code-token-function);
  }

  /* Variables, regex, namespaces, important */
  .code-token__variable,
  .token.important,
  .token.namespace,
  .token.regex,
  .token.variable {
    color: var(--lexxy-color-code-token-variable);
  }

  /* Punctuation */
  .code-token__punctuation,
  .token.punctuation {
    color: var(--lexxy-color-code-token-punctuation);
  }

  /* Tables */
  :where(.lexxy-content__table-wrapper) {
    margin: 0;
    margin-block: 1ch;
    overflow-x: auto;
  }

  table {
    border-collapse: collapse;
    border-spacing: 0;
    inline-size: calc(100% - 0.5ch);
    margin: 0.25ch;

    th,
    td {
      border: 1px solid var(--lexxy-color-ink-lighter);
      padding: 1ch;
      text-align: start;
      word-break: normal;

      *:last-child {
        margin-block-end: 0;
      }

      &.lexxy-content__table-cell--header {
        background-color: var(--lexxy-color-ink-lightest);
        font-weight: bold;
      }

      *:is(code, pre) {
        hyphens: auto;
        text-wrap: wrap;
        white-space: pre-wrap;
      }
    }
  }
}

:where([data-lexical-cursor]) {
  animation: blink 1s step-end infinite;
  block-size: 1lh;
  border-inline-start: 1px solid currentColor;
  line-height: inherit;
  margin-block: 1em;
}

/* Attachments
/* ------------------------------------------------------------------------ */

:where(.attachment) {
  block-size: auto;
  display: block;
  inline-size: fit-content;
  position: relative;
  margin-inline: auto;
  max-inline-size: 100%;
  text-align: center;

  :where(progress) {
    inline-size: 100%;
    margin: auto;
  }
}

:where(.attachment__caption) {
  color: var(--lexxy-color-text-subtle);
  font-size: var(--lexxy-text-small);

  textarea {
    background: var(--lexxy-color-canvas);
    border: none;
    color: inherit;
    font-family: inherit;
    inline-size: 100%;
    max-inline-size: 100%;
    resize: none;
    text-align: center;

    &:focus {
      outline: none;
    }

    @supports (field-sizing: content) {
      field-sizing: content;
      inline-size: auto;
      min-inline-size: 20ch;
    }
  }
}

:where(.attachment__icon) {
  aspect-ratio: 4/5;
  background-color: color-mix(var(--lexxy-attachment-icon-color), transparent 90%);
  block-size: 3lh;
  border: 2px solid var(--lexxy-attachment-icon-color);
  border-block-start-width: 1ch;
  border-radius: var(--lexxy-radius);
  box-sizing: border-box;
  color: var(--lexxy-attachment-icon-color);
  display: grid;
  font-size: var(--lexxy-text-small);
  font-weight: bold;
  inline-size: auto;
  place-content: center;
  text-transform: uppercase;
}

:where(.attachment--preview) {
  border-radius: var(--lexxy-radius);

  img, video {
    block-size: auto;
    display: block;
    margin-inline: auto;
    max-inline-size: 100%;
    user-select: none;
  }

  > a {
    display: block;
  }

  .attachment__caption {
    margin-block-start: 1ch;
  }
}

:where(.attachment--file) {
  --lexxy-attachment-icon-color: var(--lexxy-color-text-subtle);

  align-items: center;
  display: flex;
  flex-wrap: wrap;
  gap: 1ch;
  inline-size: auto;

  .attachment__caption {
    display: grid;
    flex: 1;
    text-align: start;
  }

  .attachment__name {
    color: var(--lexxy-color-ink);
    font-weight: bold;
  }
}

:where(
  .attachment--psd,
  .attachment--key,
  .attachment--sketch,
  .attachment--ai,
  .attachment--eps,
  .attachment--indd,
  .attachment--svg,
  .attachment--ppt,
  .attachment--pptx
) {
  --lexxy-attachment-icon-color: var(--lexxy-color-red);
}

:where(
  .attachment--css,
  .attachment--php,
  .attachment--json,
  .attachment--htm,
  .attachment--html,
  .attachment--rb,
  .attachment--erb,
  .attachment--ts,
  .attachment--js
) {
  --lexxy-attachment-icon-color: var(--lexxy-color-purple);
}

:where(
  .attachment--txt,
  .attachment--pages,
  .attachment--rtf,
  .attachment--md,
  .attachment--doc,
  .attachment--docx
) {
  --lexxy-attachment-icon-color: var(--lexxy-color-blue);
}

:where(
  .attachment--csv,
  .attachment--numbers,
  .attachment--xls,
  .attachment--xlsx
) {
  --lexxy-attachment-icon-color: var(--lexxy-color-green);
}

/* Horizontal divider */
:where(.horizontal-divider) {
  margin: 0;
  margin-block-end: .5em;
  padding: 1.5em 0 .5em;

  hr {
    border: 0;
    border-block-end: 1px solid currentColor;
    inline-size: 20%;
    margin: 0;
  }
}

/* Custom attachments such as mentions, etc. */
:where(action-text-attachment[content-type^="application/vnd.actiontext"]) {
  --lexxy-attachment-bg-color: transparent;
  --lexxy-attachment-image-size: 1em;
  --lexxy-attachment-text-color: currentColor;

  align-items: center;
  background: var(--lexxy-attachment-bg-color);
  border-radius: var(--lexxy-radius);
  box-shadow: -0.25ch 0 0 var(--lexxy-attachment-bg-color), 0.5ch 0 0 var(--lexxy-attachment-bg-color);
  color: var(--lexxy-attachment-text-color);
  display: inline-flex;
  gap: 0.25ch;
  margin: 0;
  padding: 0;
  position: relative;
  white-space: normal;

  img {
    block-size: var(--lexxy-attachment-image-size);
    border-radius: 50%;
    inline-size: var(--lexxy-attachment-image-size);
  }

  &.node--selected {
    --lexxy-attachment-bg-color: var(--lexxy-color-accent-dark);
    --lexxy-attachment-text-color: var(--lexxy-color-ink-inverted);
  }
}

:where(lexxy-editor) {
  --lexxy-editor-padding: 1ch;
  --lexxy-editor-rows: 8lh;
  @supports (min-block-size: attr(rows lh)) {
    --lexxy-editor-rows: attr(rows lh, 8lh);
  }

  --lexxy-toolbar-gap: 2px;

  border: 1px solid var(--lexxy-color-ink-lighter);
  border-radius: calc(var(--lexxy-radius) + var(--lexxy-toolbar-gap));
  background-color: var(--lexxy-color-canvas);
  display: block;
  overflow: visible;
  position: relative;
  transition: opacity 150ms;

  input,
  button,
  summary {
    &:focus-visible {
      outline: var(--lexxy-focus-ring-size) solid var(--lexxy-focus-ring-color);
      outline-offset: var(--lexxy-focus-ring-offset);
    }
  }

  button,
  summary {
    -webkit-appearance: none;
    appearance: none;
    background: var(--lexxy-color-canvas);
    border: none;
    border-radius: var(--lexxy-radius);
    cursor: pointer;
    font-size: inherit;
    inline-size: auto;
    padding: 0;

    @media(any-hover: hover) {
      &:hover:not([aria-disabled="true"]) {
        background: var(--lexxy-color-ink-lightest);
      }
    }
  }

  .node--selected {
    &:has(img) img,
    &:not(:has(img)) {
      outline: var(--lexxy-focus-ring-size) solid var(--lexxy-focus-ring-color);
      outline-offset: var(--lexxy-focus-ring-offset);
    }
  }

  table {
    th, td {
      &.table-cell--selected {
        background-color: var(--lexxy-color-table-cell-selected-bg);
      }

      &.lexxy-content__table-cell--selected {
        background-color: var(--lexxy-color-table-cell-selected-bg);
        border-color: var(--lexxy-color-table-cell-selected-border);
      }
    }

    &.lexxy-content__table--selection {
      ::selection {
        background: transparent;
      }
    }
  }

  action-text-attachment {
    cursor: pointer;
  }
}

/* Placeholder */
:where(.lexxy-editor--empty) {
  .lexxy-editor__content:not(:has(ul, ol))::before {
    content: attr(placeholder);
    color: currentColor;
    cursor: text;
    opacity: 0.66;
    pointer-events: none;
    position: absolute;
    white-space: pre-line;
  }
}

:where(.lexxy-editor__content) {
  min-block-size: var(--lexxy-editor-rows);
  outline: 0;
  padding: var(--lexxy-editor-padding);
}

:where(.lexxy-editor--drag-over) {
  background-color: var(--lexxy-color-selected);
  border-radius: var(--lexxy-radius);
  outline: 2px dashed var(--lexxy-color-selected-dark);
}

/* Toolbar
/* -------------------------------------------------------------------------- */

:where(lexxy-toolbar) {
  --lexxy-toolbar-icon-size: 1em;

  border-block-end: 1px solid var(--lexxy-color-ink-lighter);
  color: currentColor;
  display: flex;
  font-size: inherit;
  gap: var(--lexxy-toolbar-gap);
  max-inline-size: 100%;
  padding: 2px;
  position: relative;

  &[data-attachments="false"] button[name="upload"]{
    display: none;
  }
}

:where(.lexxy-editor__toolbar-button) {
  aspect-ratio: 1;
  block-size: 2lh;
  color: currentColor;
  display: grid;
  place-items: center;

  &:is(:active):not([aria-disabled="true"]),
  &[aria-pressed="true"] {
    background-color: var(--lexxy-color-selected);

    &:hover {
      background-color: var(--lexxy-color-selected-hover);
    }
  }

  &[aria-disabled="true"] {
    cursor: default;
    opacity: 0.3;
  }

  svg {
    -webkit-touch-callout: none;
    block-size: var(--lexxy-toolbar-icon-size);
    fill: currentColor;
    grid-area: 1/1;
    inline-size: var(--lexxy-toolbar-icon-size);
    user-select: none;
  }
}

:where(.lexxy-editor__toolbar-spacer) {
  flex: 1;
}

/* Make sure spacer is only displayed if there's another button before it */
* + :where(.lexxy-editor__toolbar-spacer) {
  min-inline-size: 1lh;
}

:where(.lexxy-editor__toolbar-overflow) {
  display: none;
  justify-self: flex-end;
  position: relative;
  z-index: 1;

  summary {
    list-style: none;

    &::-webkit-details-marker {
      display: none;
    }

    [open] & {
      background-color: var(--lexxy-color-ink-lightest);
    }
  }
}

:where(.lexxy-editor__toolbar-overflow-menu) {
  background-color: var(--lexxy-color-canvas);
  border-radius: calc(var(--lexxy-radius) + var(--lexxy-toolbar-gap));
  box-shadow: var(--lexxy-shadow);
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: var(--lexxy-toolbar-gap);
  inset-inline-end: 0;
  padding: var(--lexxy-toolbar-gap);
  position: absolute;

  .lexxy-editor__toolbar-spacer {
    display: none;
  }

  * + .lexxy-editor__toolbar-spacer + button {
    margin-left: 0.5lh;
  }
}

/* Dropdowns
/* -------------------------------------------------------------------------- */

:where(.lexxy-editor__toolbar-dropdown) {
  position: relative;
  user-select: none;
  -webkit-user-select: none;

  :where(.lexxy-editor__toolbar-dropdown-content) {
    --dropdown-padding: 1ch;
    --dropdown-gap: calc(var(--dropdown-padding) / 2);

    background-color: var(--lexxy-color-canvas);
    border: 2px solid var(--lexxy-color-selected-hover);
    border-radius: var(--lexxy-radius);
    border-start-start-radius: 0;
    box-sizing: border-box;
    color: var(--lexxy-color-ink);
    display: flex;
    gap: var(--dropdown-gap);
    inset-block-start: 2lh;
    inset-inline-start: 0;
    max-inline-size: 40ch;
    margin: 0;
    padding: var(--dropdown-padding);
    position: absolute;
    z-index: 3;
  }

  &:is([open]) .lexxy-editor__toolbar-button {
    background-color: var(--lexxy-color-selected-hover);
    border-end-end-radius: 0;
    border-end-start-radius: 0;

    &:hover {
      background-color: var(--lexxy-color-selected-hover);
    }
  }

  [overflowing] & {
    position: static;

    .lexxy-editor__toolbar-dropdown-content {
      --dropdown-padding: 0.5ch;
      inset-inline-end: var(--dropdown-padding);
      inset-inline-start: var(--dropdown-padding);
    }
  }
}

/* Link dropdown
/* -------------------------------------------------------------------------- */

:where(lexxy-link-dropdown) {

  > * { flex: 1; }

  .lexxy-editor__toolbar-dropdown-actions {
    display: flex;
    font-size: var(--lexxy-text-small);
    flex: 1 1 0%;
    gap: 1ch;
    margin-block-start: 1ch;
  }

  input[type="url"],
  button {
    line-height: 1.5lh;
    padding-inline: 1ch;
  }

  input[type="url"] {
    background-color: var(--lexxy-color-canvas);
    border: 1px solid var(--lexxy-color-ink-lighter);
    border-radius: var(--lexxy-radius);
    color: var(--lexxy-color-text);
    box-sizing: border-box;
    inline-size: 100%;
    min-inline-size: 40ch;

    [overflowing] & {
      min-inline-size: 0;
    }
  }

  button {
    background-color: var(--lexxy-color-ink-lightest);
    color: var(--lexxy-color-text);
    inline-size: 100%;
    justify-content: center;
  }

  button[type="submit"] {
    background-color: var(--lexxy-color-accent-dark);
    color: var(--lexxy-color-ink-inverted);

    &:hover {
      background-color: var(--lexxy-color-accent-medium);
    }
  }
}

/* Color dropdown
/* -------------------------------------------------------------------------- */

:where(lexxy-highlight-dropdown) {
  display: flex;
  flex-direction: column;

  [data-button-group] {
    display: flex;
    justify-items: flex-start;
    flex-direction: row;
    gap: var(--dropdown-gap);

    button {
      aspect-ratio: 1 / 1;
      inline-size: var(--button-size);
      min-inline-size: var(--button-size);
      max-inline-size: var(--button-size);

      &:after {
        align-self: center;
        content: "Aa";
        display: inline-block;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        position: absolute;
        inset-block-start: 0;
        inset-block-end: 0;
        inset-inline-end: 0;
        inset-inline-start: 0;
      }
    }
  }

  button {
    --button-size: 2lh;

    color: var(--lexxy-color-text);
    flex: 1;
    min-block-size: var(--button-size);
    position: relative;

    &:hover {
      opacity: 0.8;
    }

    &[aria-pressed="true"] {
      box-shadow: 0 0 0 2px currentColor inset;

      &:after {
        content: "✓";
      }
    }
  }

  .lexxy-editor__toolbar-dropdown-reset[disabled] {
    display: none;
  }

  [overflowing] & {
    inline-size: fit-content;

    [data-button-group] {
      flex-wrap: wrap;

      button {
        --button-size: 1.6lh;

        &:after {
          font-size: 0.9em;
        }
      }
    }
  }
}

/* Table handle buttons
/* -------------------------------------------------------------------------- */

:where(.lexxy-table-handle-buttons) {
  --button-size: 2.5lh;

  color: var(--lexxy-color-ink-inverted);
  display: none;
  flex-direction: row;
  font-size: var(--lexxy-text-small);
  gap: 0.25ch;
  line-height: 1;
  position: absolute;
  transform: translate(-50%, -120%);
  z-index: 2;

  .lexxy-table-control {
    align-items: center;
    background-color: var(--lexxy-color-ink);
    border-radius: 0.75ch;
    display: flex;
    flex-direction: row;
    gap: 1ch;
    padding: 2px;
    white-space: nowrap;

    button,
    summary {
      aspect-ratio: 1 / 1;
      align-items: center;
      background-color: transparent;
      border-radius: var(--lexxy-radius);
      border: 0;
      color: var(--lexxy-color-ink-inverted);
      cursor: pointer;
      display: flex;
      font-weight: bold;
      justify-content: center;
      line-height: 1;
      list-style: none;
      min-block-size: var(--button-size);
      min-inline-size: var(--button-size);
      padding: 0;
      user-select: none;
      -webkit-user-select: none;

      &:hover {
        background-color: var(--lexxy-color-ink-medium);
      }

      &:focus-visible {
        outline-color: var(--lexxy-focus-ring-color);
      }

      svg {
        block-size: 1em;
        inline-size: 1em;
        fill: currentColor;
      }

      span {
        display: none;
      }
    }
  }

  .lexxy-table-control__more-menu {
    gap: 0;
    padding: 0.25ch;
    position: relative;

    summary {
      &::-webkit-details-marker {
        display: none;
      }
    }

    .lexxy-table-control__more-menu-details {
      display: flex;
      flex-direction: column;
      gap: 0.25ch;
      inset-block-start: 105%;
      inset-inline-start: 0;
      padding: 0;
      position: absolute;

      .lexxy-table-control__more-menu-section {
        background: var(--lexxy-color-ink);
        border-radius: 0.75ch;
        display: flex;
        flex-direction: column;
        padding: 0.25ch;
      }

      button {
        aspect-ratio: unset;
        align-items: center;
        display: flex;
        flex-direction: row;
        font-weight: normal;
        gap: 1ch;
        justify-content: flex-start;
        padding: 0.5ch 2ch;
        padding-inline-start: 1ch;
        white-space: nowrap;

        span {
          display: inline-block;
        }

        svg {
          block-size: 1.3lh;
          inline-size: 1.3lh;
          fill: currentColor;
        }
      }
    }
  }
}


/* Language picker
/* -------------------------------------------------------------------------- */

:where(.lexxy-code-language-picker) {
  -webkit-appearance: none;
  appearance: none;
  background-color: var(--lexxy-color-canvas);
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='m12 19.5c-.7 0-1.3-.3-1.7-.8l-9.8-11.1c-.7-.8-.6-1.9.2-2.6.8-.6 1.9-.6 2.5.2l8.6 9.8c0 .1.2.1.4 0l8.6-9.8c.7-.8 1.8-.9 2.6-.2s.9 1.8.2 2.6l-9.8 11.1c-.4.5-1.1.8-1.7.8z' fill='%23000'/%3E%3C/svg%3E");
  background-position: center right 1ch;
  background-repeat: no-repeat;
  background-size: 1ch;
  block-size: 1.5lh;
  border: 1px solid var(--lexxy-color-ink-lighter);
  border-radius: var(--lexxy-radius);
  color: var(--lexxy-color-ink);
  font-family: var(--lexxy-font-base);
  font-size: var(--lexxy-text-small);
  font-weight: normal;
  inset-inline-end: var(--lexxy-editor-padding);
  margin: 0.5ch 0.5ch 0 -0.5ch;
  padding: 0 2ch 0 1ch;
  text-align: start;

  @media (prefers-color-scheme: dark) {
    background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='m12 19.5c-.7 0-1.3-.3-1.7-.8l-9.8-11.1c-.7-.8-.6-1.9.2-2.6.8-.6 1.9-.6 2.5.2l8.6 9.8c0 .1.2.1.4 0l8.6-9.8c.7-.8 1.8-.9 2.6-.2s.9 1.8.2 2.6l-9.8 11.1c-.4.5-1.1.8-1.7.8z' fill='%23fff'/%3E%3C/svg%3E");
  }
}

/* Prompt
/* ------------------------------------------------------------------------ */

:where(.lexxy-prompt-menu) {
  --lexxy-prompt-avatar-size: 24px;
  --lexxy-prompt-min-width: 20ch;
  --lexxy-prompt-padding: 0.5ch;

  background-color: var(--lexxy-color-canvas);
  border-radius: calc(var(--lexxy-prompt-padding) * 2);
  box-shadow: var(--lexxy-shadow);
  color: var(--lexxy-color-ink);
  font-family: var(--lexxy-font-base);
  font-size: var(--lexxy-text-small);
  list-style: none;
  margin: 0;
  max-block-size: 200px;
  min-inline-size: var(--lexxy-prompt-min-width);
  overflow: auto;
  padding: var(--lexxy-prompt-padding);
  visibility: hidden;
  z-index: var(--lexxy-z-popup);
}

:where(.lexxy-prompt-menu--visible) {
  visibility: initial;
}

:where(.lexxy-prompt-menu__item) {
  align-items: center;
  border-radius: var(--lexxy-radius);
  cursor: pointer;
  display: flex;
  gap: var(--lexxy-prompt-padding);
  padding: var(--lexxy-prompt-padding);
  white-space: nowrap;

  &:hover {
    background-color: var(--lexxy-color-ink-lightest);
  }

  &[aria-selected] {
    background-color: var(--lexxy-color-selected);
  }

  img {
    block-size: var(--lexxy-prompt-avatar-size);
    border-radius: 50%;
    flex-shrink: 0;
    inline-size: var(--lexxy-prompt-avatar-size);
    margin: 0;
  }

  + & {
    margin-top: 2px;
  }
}

:where(.lexxy-prompt-menu__item--empty) {
  color: var(--lexxy-color-ink-medium);
  padding: var(--lexxy-prompt-padding);
}

</style>
