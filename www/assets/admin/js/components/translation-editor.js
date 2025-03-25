export class TranslationEditor {
  constructor() {
    this.table = document.querySelector("#translationEditorTable tbody");
    this.hiddenTargetLang = document.querySelector("input[name='target_lang']");
    this.hiddenTranslations = document.querySelector("input[name='translations']");

    // Settings
    this.MAX_HEIGHT = 155;
  }

  init() {
    document.getElementById("addRow").addEventListener("click", () => this.addRow());
    document.querySelectorAll(".remove-row").forEach(button => this.addRemoveListener(button));
    document.querySelectorAll(".copy-text").forEach(button => this.addCopyListener(button));
    this.addSubmitListener(document.querySelector(".translationEditorForm"));
    this.keyboardEvents();
    this.initTextareaSizing();
  }

  addRow(key = "", sourceText = "", targetText = "") {
    const tr = document.createElement("tr");
    tr.classList.add("translation-row")

    tr.innerHTML = `
      <td class="align-middle status-col status-new"></td>
      <td class="align-top key-col"><input type="text" class="form-control key-input" value="${key}"></td>
      <td class="align-middle"><textarea class="form-control linked-textarea source-text" rows="1">${sourceText}</textarea></td>
      <td class="align-middle copy-col"><button type="button" class="btn btn-primary copy-text" tabindex=-1><i class="fa-solid fa-right-from-bracket"></i></i></button></td>
      <td class="align-middle"><textarea class="form-control linked-textarea target-text" rows="1">${targetText}</textarea></td>
      <td class="align-middle action-col"><button type="button" class="btn btn-danger remove-row" tabindex=-1><i class="fa-solid fa-minus"></i></button></td>
    `;

    this.table.appendChild(tr);
    this.addTextareaSizing(tr);
    this.addRemoveListener(tr.querySelector(".remove-row"));
    this.addCopyListener(tr.querySelector(".copy-text"));
    this.addCodeInputListener(tr.querySelector(".key-input"));
    tr.querySelector(".key-input").focus();
  }

  addCodeInputListener(input) {
    input.addEventListener("input", function (event) {
      let value = event.target.value.toLowerCase();
      value = value.replace(/[^a-z0-9_.-]+/g, "_");
      value = value.replace(/__+/g, "_");
      value = value.replace(/^_+/g, "");
      event.target.value = value;

      const row = event.target.closest("tr");
      if (row) {
        this.markChangedRow(row, "status-changed");
      }
    }.bind(this));
    input.addEventListener("keydown", function (event) {
      if (event.key === "Enter") {
        event.preventDefault();
      }
    });
  }

  addRemoveListener(button) {
    button.addEventListener("click", function () {
      const row = this.closest("tr");
      row.remove();
    });
  }

  addCopyListener(button) {
    const row = button.closest("tr");
    const sourceText = row.querySelector(".source-text");
    const targetText = row.querySelector(".target-text");

    function updateButtonState() {
      if (targetText.value.trim() === "") {
        button.classList.add("btn-primary");
        button.classList.remove("btn-secondary");
        button.disabled = false;
      } else {
        button.classList.add("btn-secondary");
        button.classList.remove("btn-primary");
        button.disabled = true;
      }
    }
    updateButtonState();

    button.addEventListener("click", function () {
      targetText.value = sourceText.value;
      updateButtonState();
    });

    targetText.addEventListener("input", updateButtonState);
  }

  addSubmitListener(form) {
    form.addEventListener("submit", function (event) {
      const rows = this.table.querySelectorAll("tr");

      let data = {};
      let keyValues = new Map();
      let hasDuplicate = false;
      let hasNoKeyValues = false;

      document.querySelectorAll(".key-input").forEach(input => {
        input.classList.remove("is-invalid");
      });

      rows.forEach(row => {
        const keyInput = row.querySelector(".key-input");
        const key = keyInput.value.trim();
        const keyLower = keyInput.value.trim().toLowerCase();
        const sourceText = row.querySelector(".source-text").value.trim();
        const targetText = row.querySelector(".target-text").value.trim();
        const targetLang = this.hiddenTargetLang.value;

        if (key && (sourceText || targetLang)) {
          if (keyValues.has(keyLower)) {
            hasDuplicate = true;
            keyInput.classList.add("is-invalid");
            keyValues.get(keyLower).classList.add("is-invalid");
            this.markChangedRow(row, "status-error");
            this.markChangedRow(keyValues.get(keyLower).closest("tr"), "status-error");
          } else {
            keyValues.set(keyLower, keyInput);
            this.markChangedRow(keyInput.closest("tr"), "");
          }

          if (!data[key]) data[key] = {};
          data[key]["default"] = sourceText;
          data[key][targetLang] = targetText;
        } else if (sourceText || targetText) {
          hasNoKeyValues = true;
          keyInput.classList.add("is-invalid");
          this.markChangedRow(row, "status-error");
        }
      });

      if (hasDuplicate) {
        alert("Duplicitní klíče nejsou povoleny! Opravte je před odesláním.");
      }

      if (hasNoKeyValues) {
        alert("Formulář obsahuje položky bez klíčů. Duplňte klíče, nebo položky odstraňte.");
      }

      if (hasDuplicate || hasNoKeyValues) {
        event.preventDefault();
      } else {
        this.hiddenTranslations.value = JSON.stringify(data);
      }
    }.bind(this));
  }

  keyboardEvents() {
    document.addEventListener("keydown", function (event) {
      if (event.key === "Insert") { // Insert: Add new row
        event.preventDefault();
        this.addRow();
      }
      if (event.ctrlKey && event.key === "s") { // Ctrl+S: Submit
        event.preventDefault();
        document.querySelector(".translationEditorForm input[type='submit']").click();
      }
      if (event.ctrlKey && event.key === "r") { // Ctrl+R: (Reload prevention)
        event.preventDefault();
      }
    }.bind(this));
  }

  // Textarea auto-resize
  initTextareaSizing() {
    this.addTextareaSizing(document);

    document.querySelectorAll(".translation-row").forEach(row => {
      const areas = row.querySelectorAll(".linked-textarea");
      if (areas.length === 2) {
        this.adjustHeight(areas[0], areas[1]);
      }
    });
  }

  addTextareaSizing(parent) {
    parent.querySelectorAll(".linked-textarea").forEach(textarea => {
      textarea.addEventListener("input", function (event) {
        this.syncHeight(event);
        const row = event.target.closest("tr");
        if (row) {
          this.markChangedRow(row, "status-changed");
        }
      }.bind(this));
      textarea.addEventListener("mousedown", event => this.syncHeight(event));
    });
  }

  syncHeight(event) {
    const area1 = event.target;
    const area2 = this.getPairedTextarea(area1);
    if (!area2) return;

    this.adjustHeight(area1, area2);
  }

  adjustHeight(area1, area2) {
    area1.style.height = "auto";
    area2.style.height = "auto";

    let newHeight = Math.max(area1.scrollHeight, area2.scrollHeight);

    if (newHeight > this.MAX_HEIGHT) {
      newHeight = this.MAX_HEIGHT;
      area1.style.overflowY = "auto";
      area2.style.overflowY = "auto";
    } else {
      area1.style.overflowY = "hidden";
      area2.style.overflowY = "hidden";
    }

    area1.style.height = area2.style.height = newHeight + "px";
  }

  getPairedTextarea(textarea) {
    const row = textarea.closest(".translation-row");
    return row ? row.querySelectorAll(".linked-textarea")[0] === textarea
      ? row.querySelectorAll(".linked-textarea")[1]
      : row.querySelectorAll(".linked-textarea")[0]
      : null;
  }

  // Row status
  markChangedRow(row, statusClass) {
    const statusCell = row.querySelector(".status-col");
    if (!statusCell) {
      return;
    }

    if (statusCell.classList.contains("status-new") && statusClass === "status-changed") {
      return;
    }

    if (statusCell.classList.contains("status-changed") && statusClass === "") {
      return;
    }

    statusCell.classList.remove("status-changed", "status-new", "status-error");
    if (statusClass) {
      statusCell.classList.add(statusClass);
    }
  }
}
