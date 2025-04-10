document.addEventListener("DOMContentLoaded", () => {
  // Declare XLSX variable
  let XLSX

  // Check if XLSX is available (e.g., from a script tag)
  if (typeof window !== "undefined" && window.XLSX) {
    XLSX = window.XLSX
  } else {
    console.error("XLSX library not found. Make sure to include it in your HTML.")
    return // Exit if XLSX is not available
  }

  // File upload handling
  const fileDropArea = document.querySelector(".file-drop-area")
  const fileInput = document.querySelector(".file-input")
  const fileInfo = document.querySelector(".file-info")

  if (fileDropArea && fileInput) {
    // Handle drag and drop events
    ;["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
      fileDropArea.addEventListener(eventName, preventDefaults, false)
    })

    function preventDefaults(e) {
      e.preventDefault()
      e.stopPropagation()
    }
    ;["dragenter", "dragover"].forEach((eventName) => {
      fileDropArea.addEventListener(eventName, highlight, false)
    })
    ;["dragleave", "drop"].forEach((eventName) => {
      fileDropArea.addEventListener(eventName, unhighlight, false)
    })

    function highlight() {
      fileDropArea.classList.add("active")
    }

    function unhighlight() {
      fileDropArea.classList.remove("active")
    }

    fileDropArea.addEventListener("drop", handleDrop, false)

    function handleDrop(e) {
      const dt = e.dataTransfer
      const files = dt.files

      if (files.length) {
        fileInput.files = files
        updateFileInfo(files[0])

        // If it's an Excel file, try to parse it
        if (files[0].name.endsWith(".xlsx") || files[0].name.endsWith(".xls")) {
          parseExcelFile(files[0])
        }
      }
    }

    fileInput.addEventListener("change", function () {
      if (this.files.length) {
        updateFileInfo(this.files[0])

        // If it's an Excel file, try to parse it
        if (this.files[0].name.endsWith(".xlsx") || this.files[0].name.endsWith(".xls")) {
          parseExcelFile(this.files[0])
        }
      }
    })

    function updateFileInfo(file) {
      const fileSize = (file.size / 1024).toFixed(2)
      fileInfo.innerHTML = `
                <div class="d-flex align-items-center bg-light p-2 rounded">
                    <i class="bi bi-file-earmark-spreadsheet fs-4 text-primary me-2"></i>
                    <div>
                        <p class="mb-0 fw-medium">${file.name}</p>
                        <p class="mb-0 small text-muted">${fileSize} KB</p>
                    </div>
                    <button type="button" class="btn-close ms-auto remove-file" aria-label="Remove file"></button>
                </div>
            `

      // Add event listener to remove file button
      document.querySelector(".remove-file").addEventListener("click", () => {
        fileInput.value = ""
        fileInfo.innerHTML = ""
      })
    }

    function parseExcelFile(file) {
      const reader = new FileReader()

      reader.onload = (e) => {
        const data = new Uint8Array(e.target.result)
        const workbook = XLSX.read(data, { type: "array" })
        const firstSheet = workbook.Sheets[workbook.SheetNames[0]]
        const jsonData = XLSX.utils.sheet_to_json(firstSheet)

        if (jsonData.length > 0) {
          const firstRow = jsonData[0]

          // Update form with data from Excel
          if (document.getElementById("caseNumber")) {
            document.getElementById("caseNumber").value = firstRow.CaseNumber || firstRow["Case Number"] || ""
          }

          if (document.getElementById("caseTitle")) {
            document.getElementById("caseTitle").value = firstRow.CaseTitle || firstRow["Case Title"] || ""
          }

          if (document.getElementById("officerName")) {
            document.getElementById("officerName").value = firstRow.OfficerName || firstRow["Officer Name"] || ""
          }

          if (document.getElementById("rank")) {
            document.getElementById("rank").value = firstRow.Rank || ""
          }

          if (document.getElementById("caseDescription")) {
            document.getElementById("caseDescription").value =
              firstRow.Description || firstRow["Case Description"] || ""
          }
        }
      }

      reader.readAsArrayBuffer(file)
    }
  }

  // Add respondent row in case detail page
  const addRespondentBtn = document.querySelector(".add-respondent-btn")

  if (addRespondentBtn) {
    addRespondentBtn.addEventListener("click", () => {
      const respondentTable = document.querySelector(".respondent-table tbody")
      const caseNumber = document.querySelector('[name="caseNumber"]').value

      const newRow = document.createElement("tr")
      newRow.innerHTML = `
                <td>${caseNumber}</td>
                <td><input type="text" name="respondent_name[]" class="form-control form-control-sm" required></td>
                <td>
                    <select name="respondent_rank[]" class="form-select form-select-sm">
                        <option value="Officer">Officer</option>
                        <option value="Sergeant">Sergeant</option>
                        <option value="Lieutenant">Lieutenant</option>
                        <option value="Captain">Captain</option>
                        <option value="Inspector">Inspector</option>
                        <option value="Civilian" selected>Civilian</option>
                    </select>
                </td>
                <td>
                    <select name="respondent_unit[]" class="form-select form-select-sm">
                        <option value="Headquarters">Headquarters</option>
                        <option value="Patrol">Patrol</option>
                        <option value="Investigation">Investigation</option>
                        <option value="N/A" selected>N/A</option>
                    </select>
                </td>
                <td>
                    <select name="respondent_justification[]" class="form-select form-select-sm">
                        <option value="Primary Suspect" selected>Primary Suspect</option>
                        <option value="Accomplice">Accomplice</option>
                        <option value="Witness">Witness</option>
                    </select>
                </td>
                <td><input type="text" name="respondent_remarks[]" class="form-control form-control-sm"></td>
            `

      respondentTable.appendChild(newRow)
    })
  }
})

