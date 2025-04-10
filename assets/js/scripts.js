// File upload handling
document.addEventListener("DOMContentLoaded", () => {
  const fileUploadArea = document.getElementById("file-upload-area")
  const fileInput = document.getElementById("file-input")
  const filePreview = document.getElementById("file-preview")

  if (fileUploadArea && fileInput) {
    // Handle click on upload area
    fileUploadArea.addEventListener("click", () => {
      fileInput.click()
    })

    // Handle drag events
    fileUploadArea.addEventListener("dragover", (e) => {
      e.preventDefault()
      fileUploadArea.classList.add("dragging")
    })

    fileUploadArea.addEventListener("dragleave", () => {
      fileUploadArea.classList.remove("dragging")
    })

    fileUploadArea.addEventListener("drop", (e) => {
      e.preventDefault()
      fileUploadArea.classList.remove("dragging")

      if (e.dataTransfer.files.length) {
        fileInput.files = e.dataTransfer.files
        handleFileSelection(e.dataTransfer.files[0])
      }
    })

    // Handle file selection
    fileInput.addEventListener("change", () => {
      if (fileInput.files.length) {
        handleFileSelection(fileInput.files[0])
      }
    })

    function handleFileSelection(file) {
      // Show file preview
      if (filePreview) {
        const fileSize = (file.size / 1024).toFixed(2)
        let fileIcon = "fa-file"

        // Determine file icon based on type
        if (file.type.includes("excel") || file.type.includes("spreadsheet")) {
          fileIcon = "fa-file-excel"
        } else if (file.type.includes("pdf")) {
          fileIcon = "fa-file-pdf"
        } else if (file.type.includes("word")) {
          fileIcon = "fa-file-word"
        } else if (file.type.includes("image")) {
          fileIcon = "fa-file-image"
        }

        filePreview.innerHTML = `
                    <div class="file-info">
                        <i class="fas ${fileIcon} file-icon"></i>
                        <div>
                            <p class="mb-0 fw-medium">${file.name}</p>
                            <small class="text-muted">${fileSize} KB</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="remove-file">
                        <i class="fas fa-times"></i>
                    </button>
                `

        filePreview.style.display = "flex"

        // Handle remove button
        document.getElementById("remove-file").addEventListener("click", (e) => {
          e.stopPropagation()
          fileInput.value = ""
          filePreview.style.display = "none"
        })

        // If it's an Excel file, try to parse it
        if (file.type.includes("excel") || file.type.includes("spreadsheet")) {
          parseExcelFile(file)
        }
      }
    }

    function parseExcelFile(file) {
      // In a real application, you would use a library like SheetJS to parse Excel files
      // For this example, we'll just simulate the parsing
      console.log("Parsing Excel file:", file.name)

      // Simulate form population after a delay
      setTimeout(() => {
        // This is just a simulation - in a real app, you'd extract actual data
        document.getElementById("case-number").value = "PD-" + Math.floor(Math.random() * 10000)
        document.getElementById("case-title").value = "Sample Case from Excel"
        document.getElementById("officer-name").value = "John Smith"
        document.getElementById("case-description").value = "This data was extracted from the uploaded Excel file."
      }, 1000)
    }
  }

  // Print functionality for case details
  const printButton = document.getElementById("print-button")
  if (printButton) {
    printButton.addEventListener("click", () => {
      window.print()
    })
  }
})

