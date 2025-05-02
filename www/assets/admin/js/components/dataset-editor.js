export class DatasetEditor {
  constructor() {
    this.table = document.querySelector("#datasetEditorTable tbody");
    this.hiddenColumns = document.querySelector("input[name='columns']");
  }

  init() {
    document.getElementById("addRow").addEventListener("click", () => this.addRow());
    this.table.querySelectorAll(".name-input").forEach(input => this.addNameInputListener(input));
    this.table.querySelectorAll(".slug-input").forEach(input => this.addSlugInputListener(input));
    this.addSubmitListener(document.querySelector(".datasetEditorForm"));
    this.keyboardEvents();
  }

  addRow() {
    const tr = document.createElement("tr");
    tr.classList.add("dataset-row")
    tr.innerHTML = document.querySelector(".dataset-row-template").innerHTML;

    this.table.appendChild(tr);
    this.addRemoveListener(tr.querySelector(".remove-row"));
    this.addNameInputListener(tr.querySelector(".name-input"));
    this.addSlugInputListener(tr.querySelector(".slug-input"));
    tr.querySelector(".name-input").focus();
  }

  addNameInputListener(input) {
    input.addEventListener("input", function (event) {
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

  addSlugInputListener(input) {
    input.addEventListener("input", function (event) {
      let value = event.target.value.toLowerCase();
      value = value.replace(/[^a-z0-9]+/g, "_");
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
      const rows = this.table.querySelectorAll(".dataset-row");

      let columns = {};
      let slugValues = new Map();
      let hasDuplicate = false;
      let lastID = 0;

      this.table.querySelectorAll(".slug-input").forEach(input => {
        input.classList.remove("is-invalid");
      });

      rows.forEach(row => {
        let columnID = Number(row.querySelector(".id-input").value);
        const slugInput = row.querySelector(".slug-input");
        const columnName = row.querySelector(".name-input").value.trim();
        let columnSlug = row.querySelector(".slug-input").value.trim();
        const columnType = row.querySelector(".type-input").value;
        const columnRequired = row.querySelector(".required-input").checked;
        const columnListed = row.querySelector(".listed-input").checked;
        const columnHidden = row.querySelector(".hidden-input").checked;
        const columnDeleted = row.querySelector(".deleted-input").checked;

        if (!columnSlug) {
          columnSlug = this.slugize(columnName);
          row.querySelector(".slug-input").value = columnSlug;
        }

        if (!columnID) {
          columnID = lastID + 1;
        }

        lastID = Number(columnID);

        if (slugValues.has(columnSlug)) {
          hasDuplicate = true;
          slugInput.classList.add("is-invalid");
          slugValues.get(columnSlug).classList.add("is-invalid");
          this.markChangedRow(row, "status-error");
          this.markChangedRow(slugValues.get(columnSlug).closest("tr"), "status-error");
        } else {
          slugValues.set(columnSlug, slugInput);
          this.markChangedRow(slugInput.closest("tr"), "");
        }

        columns[columnID] = {
          id: columnID,
          name: columnName,
          slug: columnSlug,
          type: columnType,
          required: columnRequired,
          listed: columnListed,
          hidden: columnHidden,
          deleted: columnDeleted
        };
      });

      if (hasDuplicate) {
        alert("Duplicitní slugy nejsou povoleny! Opravte je před odesláním.");
      }

      if (hasDuplicate) {
        event.preventDefault();
      } else {
        this.hiddenColumns.value = JSON.stringify(columns);
        console.log(this.hiddenColumns.value);
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
        document.querySelector(".datasetEditorForm input[type='submit']").click();
      }
    }.bind(this));
  }

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

  slugize(value) {
    value = value.toLowerCase();
    value = value.replace(/[^a-z0-9]+/g, "_");
    value = value.replace(/__+/g, "_");
    value = value.replace(/^_+/g, "");
    return value;
  }
}
