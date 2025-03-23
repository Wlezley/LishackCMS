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
    tr.querySelector(".key-input").focus();
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

      rows.forEach(row => {
        const keyInput = row.querySelector(".key-input");
        const key = keyInput.value.trim();
        const sourceText = row.querySelector(".source-text").value.trim();
        const targetText = row.querySelector(".target-text").value.trim();
        const targetLang = this.hiddenTargetLang.value;

        if (key && (sourceText || targetLang)) {
          if (keyValues.has(key)) {
            hasDuplicate = true;
            keyInput.classList.add("is-invalid");
            keyValues.get(key).classList.add("is-invalid");
          } else {
            keyValues.set(key, keyInput);
            keyInput.classList.remove("is-invalid");
          }

          if (!data[key]) data[key] = {};
          data[key]["default"] = sourceText;
          data[key][targetLang] = targetText;
        }
      });

      if (hasDuplicate) {
        event.preventDefault();
        alert("Duplicitní klíče nejsou povoleny! Opravte je před odesláním.");
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
      textarea.addEventListener("input", event => this.syncHeight(event));
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
}
