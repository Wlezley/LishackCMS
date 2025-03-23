export class ConfigEditor {
  constructor() {
    this.table = document.querySelector("#configEditorTable tbody");
    this.hiddenConfiguration = document.querySelector("input[name='configuration']");

    // Settings
    this.MAX_HEIGHT = 155;
  }

  init() {
    document.getElementById("addRow").addEventListener("click", () => this.addRow());
    this.table.querySelectorAll(".remove-row").forEach(button => this.addRemoveListener(button));
    this.table.querySelectorAll(".category-input").forEach(input => this.addCodeInputListener(input));
    this.addSubmitListener(document.querySelector(".configEditorForm"));
    this.keyboardEvents();
    this.initTextareaSizing(this.table);
  }

  addRow(key = "", category = "", value = "") {
    const tr = document.createElement("tr");
    tr.classList.add("config-row")

    tr.innerHTML = `
      <td class="align-middle status-col status-new"></td>
      <td class="align-top key-col"><input type="text" class="form-control key-input" value="${key}"></td>
      <td class="align-top category-col"><input type="text" class="form-control category-input" value="${category}"></td>
      <td class="align-middle"><textarea class="form-control linked-textarea config-value" rows="1">${value}</textarea></td>
      <td class="align-middle action-col"><button type="button" class="btn btn-danger remove-row" tabindex=-1><i class="fa-solid fa-minus"></i></button></td>
    `;

    this.table.appendChild(tr);
    this.addTextareaSizing(tr);
    this.addRemoveListener(tr.querySelector(".remove-row"));
    this.addCodeInputListener(tr.querySelector(".key-input"));
    this.addCodeInputListener(tr.querySelector(".category-input"));
    tr.querySelector(".key-input").focus();
  }

  addCodeInputListener(input) {
    input.addEventListener("input", function (event) {
      let value = event.target.value.toUpperCase();
      value = value.replace(/[^A-Z0-9]+/g, "_");
      value = value.replace(/__+/g, "_");
      value = value.replace(/^_+/g, "");
      event.target.value = value;

      const row = event.target.closest("tr");
      if (row) {
        this.markChangedRow(row, "status-changed");
      }
    }.bind(this));
    input.addEventListener("blur", function (event) {
      let value = event.target.value;
      value = value.replace(/^_+|_+$/g, "");
      event.target.value = value;
    });
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
        const configCategory = row.querySelector(".category-input").value.trim();
        const configValue = row.querySelector(".config-value").value.trim();

        if (key) {
          if (keyValues.has(key)) {
            hasDuplicate = true;
            keyInput.classList.add("is-invalid");
            keyValues.get(key).classList.add("is-invalid");
            this.markChangedRow(row, "status-error");
            this.markChangedRow(keyValues.get(key).closest("tr"), "status-error");
          } else {
            keyValues.set(key, keyInput);
            this.markChangedRow(keyInput.closest("tr"), "");
          }

          data[key] = {
            category: configCategory,
            value: configValue
          };
        } else if (configCategory || configValue) {
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
        this.hiddenConfiguration.value = JSON.stringify(data);
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
        document.querySelector(".configEditorForm input[type='submit']").click();
      }
    }.bind(this));
  }

  // Textarea auto-resize
  initTextareaSizing(table) {
    this.addTextareaSizing(table);

    table.querySelectorAll(".linked-textarea").forEach(textarea => {
      this.adjustHeight(textarea);
    });
  }

  addTextareaSizing(parent) {
    parent.querySelectorAll(".linked-textarea").forEach(textarea => {
      textarea.addEventListener("input", function (event) {
        this.adjustHeight(event.target);
        const row = event.target.closest("tr");
        if (row) {
          this.markChangedRow(row, "status-changed");
        }
      }.bind(this));
      textarea.addEventListener("mousedown", () => this.adjustHeight(textarea));
    });
  }

  adjustHeight(textarea) {
    textarea.style.height = "auto";
    let newHeight = textarea.scrollHeight;

    if (newHeight > this.MAX_HEIGHT) {
      newHeight = this.MAX_HEIGHT;
      textarea.style.overflowY = "auto";
    } else {
      textarea.style.overflowY = "hidden";
    }

    textarea.style.height = newHeight + "px";
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
