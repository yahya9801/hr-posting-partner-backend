
<script>
document.addEventListener('DOMContentLoaded', function () {
  const editControls = document.querySelectorAll('.editControls a');
  editControls.forEach(function (control) {
    function btnFormatBlock() {
      document.execCommand('formatBlock', false, this.dataset.role);
    }

    function btnFormatOther() {
      document.execCommand(this.dataset.role, false, null);
    }

    function findEditor() {
      let parent = this.parentElement;
      while (parent && !parent.classList.contains('editControls')) {
        parent = parent.parentElement;
      }
      return parent.nextElementSibling;
    }

    function btnCleanHtml() {
      let selection = window.getSelection();
      if (selection.rangeCount > 0 && selection.toString().length) {
        let purify = String(selection.toString());
        let range = selection.getRangeAt(0);
        range.deleteContents();
        range.insertNode(document.createTextNode(purify));
      } else {
        let editor = findEditor.call(this);
        editor.innerHTML = stripHtmlTags(editor.innerHTML);
      }
    }

    function btnLink() {
      let linkUrl = prompt('Enter the URL of the link:');
      if (linkUrl && linkUrl !== '') {
        document.execCommand('createlink', false, linkUrl);
      }
    }

    function focusEditor() {
      let editor = findEditor.call(this);
      editor.focus();
    }

    function stripHtmlTags(str) {
      let parser = new DOMParser();
      let parsed = parser.parseFromString(str, 'text/html');
      return parsed.body.textContent || '';
    }

    function btnPasteCleanHtml() {
      navigator.clipboard.readText()
        .then(clipboardText => {
          document.execCommand('insertHTML', false, stripHtmlTags(clipboardText));
        })
        .catch(err => {
          console.error('Error paste:', err);
        });
    }

    function btnSourceCode() {
      let editor = findEditor.call(this);
      let editorSource = editor.innerHTML;
      const selector = 'editor--source-code';
      if (!editor.classList.contains(selector)) {
        editor.classList.add(selector);
        editor.innerHTML = editorSource.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      } else {
        editor.classList.remove(selector);
        editor.innerHTML = editorSource.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&');
      }
    }

    control.addEventListener('click', function (e) {
      e.preventDefault();

      if (['h1', 'h2', 'h3', 'h4', 'h5', 'p'].includes(this.dataset.role)) {
        btnFormatBlock.call(this);
      } else if ('clean' === this.dataset.role) {
        btnCleanHtml.call(this);
      } else if ('a' === this.dataset.role) {
        btnLink();
      } else if ('pasteTextClean' === this.dataset.role) {
        btnPasteCleanHtml();
      } else if ('sourceCode' === this.dataset.role) {
        btnSourceCode.call(this);
      } else {
        btnFormatOther.call(this);
      }

      focusEditor.call(this);
    });
  });
});

</script>
