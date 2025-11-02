const submitBtn_step1 = document.getElementById("first_button_submit");
const submitBtn_step2 = document.getElementById("second_button_submit");
const submitBtn_step3 = document.getElementById("third_button_submit");


const step_one_div = document.getElementById("forms_container_id"); // yung div ng forms step 1
const step_two_div = document.getElementById("requirements_container_id");
const step_3_div = document.getElementById("application_details_container_id")

// Back buttons (queried from each section to avoid changing HTML)
const backBtn_step2 = document.querySelector('#requirements_container_id .back_button_container button');
const backBtn_step3 = document.querySelector('#application_details_container_id .back_button_container button');

// Reusable animation helper - can be used for any element
function animateElement(element, animationClass, callback) {
  element.classList.add(animationClass);
  element.addEventListener('animationend', function handler() {
    element.removeEventListener('animationend', handler);
    element.classList.remove(animationClass);
    if (callback) callback();
  });
}

// Smooth show/hide helper with reusable animations
function showSection(targetDiv, animationType = 'fade') {
  const sections = [step_one_div, step_two_div, step_3_div];
  const current = sections.find(sec => sec && sec.style.display !== 'none');

  // If no current (first load), just show target
  if (!current) {
    targetDiv.style.display = 'block';
    animateElement(targetDiv, 'fade-in');
    return;
  }

  // Animate out current section
  animateElement(current, 'fade-out', () => {
    current.style.display = 'none';
    targetDiv.style.display = 'block';
    animateElement(targetDiv, 'fade-in');
  });
}
// ===== Step indicator handling =====
function setActiveStep(step) {
  // Circles
  const step1Circle = document.querySelector('.circle_shape_container');
  const step2Circle = document.querySelector('.circle_two_shape_container');
  const step3Circle = document.querySelector('.circle_three_shape_container');

  const step1IconWrap = document.querySelector('.step_one_icon_container');
  const step2IconWrap = document.querySelector('.step_two_icon_container');
  const step3IconWrap = document.querySelector('.step_three_icon_container');

  const step1Svg = step1Circle?.querySelector('svg');
  const step2Svg = step2Circle?.querySelector('svg');
  const step3Svg = step3Circle?.querySelector('svg');

  const green = getComputedStyle(document.documentElement).getPropertyValue('--color-green-base') || '#2e7d32';
  const gray = getComputedStyle(document.documentElement).getPropertyValue('--color-gray') || 'gray';

  function activate(circle, wrap, svg) {
    if (!circle || !wrap || !svg) return;
    circle.style.backgroundColor = green.trim();
    circle.style.borderColor = green.trim();
    wrap.style.borderColor = green.trim();
    svg.setAttribute('stroke', 'white');
  }

  function deactivate(circle, wrap, svg) {
    if (!circle || !wrap || !svg) return;
    circle.style.backgroundColor = 'transparent';
    circle.style.borderColor = gray.trim();
    wrap.style.borderColor = gray.trim();
    svg.setAttribute('stroke', 'gray');
  }

  // reset
  deactivate(step2Circle, step2IconWrap, step2Svg);
  deactivate(step3Circle, step3IconWrap, step3Svg);
  // step1 is default active in UI; ensure it's active when step==1
  if (step === 1) {
    activate(step1Circle, step1IconWrap, step1Svg);
  }
  if (step >= 2) {
    activate(step2Circle, step2IconWrap, step2Svg);
  }
  if (step >= 3) {
    activate(step3Circle, step3IconWrap, step3Svg);
  }

  // Descriptions (second <p> inside each .step_one_description block)
  const labels = document.querySelectorAll('.steps_description_container .step_one_description p:nth-child(2)');
  labels.forEach((el, idx) => {
    const isActive = (idx + 1) <= step && (idx + 1) === step; // highlight current step
    el.classList.toggle('active_Step_description', isActive);
    el.classList.toggle('active_Step3_description', isActive && step === 3);
    if (!isActive) {
      el.classList.remove('active_Step3_description');
    }
  });
}



// ===== Validation Functions =====
function validateStep1() {
  const requiredFields = [
    { id: 'firstName', name: 'First Name' },
    { id: 'lastName', name: 'Last Name' },
    { id: 'gender', name: 'Gender' },
    { id: 'birthday', name: 'Birthday' },
    { id: 'contactNumber', name: 'Contact Number' },
    { id: 'houseNumber', name: 'House/Unit/Building Number' },
    { id: 'streetName', name: 'Street Name' },
    { id: 'city', name: 'City/Municipality' },
    { id: 'barangay', name: 'Barangay' }
  ];

  let isValid = true;
  let emptyFields = [];

  // Clear previous error styles
  requiredFields.forEach(field => {
    const element = document.getElementById(field.id);
    if (element) {
      element.style.borderColor = '';
      element.style.boxShadow = '';
    }
  });

  // Check each required field
  requiredFields.forEach(field => {
    const element = document.getElementById(field.id);
    if (element) {
      const value = element.value.trim();
      if (!value || value === '') {
        isValid = false;
        emptyFields.push(field.name);
        // Add error styling
        element.style.borderColor = '#dc2626';
        element.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
      }
    }
  });

  // Additional validation for contact number format
  if (isValid) {
    const contactNumberElement = document.getElementById('contactNumber');
    if (contactNumberElement) {
      const contactNumber = contactNumberElement.value.trim();
      const contactPattern = /^09[0-9]{9}$/;

      if (contactNumber && !contactPattern.test(contactNumber)) {
        isValid = false;
        contactNumberElement.style.borderColor = '#dc2626';
        contactNumberElement.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';

        messageModalV1Show({
          icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-circle"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r="0.01"/></svg>`,
          iconBg: '#fef3c7',
          actionBtnBg: '#f59e0b',
          showCancelBtn: false,
          title: 'Invalid Contact Number',
          message: 'Please enter a valid Philippine mobile number starting with 09 (e.g., 09123456789)',
          cancelText: 'Cancel',
          actionText: 'OK, I\'ll fix it',
          onConfirm: () => {
            messageModalV1Dismiss();
            contactNumberElement.focus();
          }
        });
        return isValid;
      }
    }
  }

  if (!isValid && emptyFields.length > 0) {
    messageModalV1Show({
      icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-circle"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><circle cx="12" cy="16" r="0.01"/></svg>`,
      iconBg: '#fef3c7',
      actionBtnBg: '#f59e0b',
      showCancelBtn: false,
      title: 'Required Fields Missing',
      message: `Please fill in the following required fields:<br><br>• ${emptyFields.join('<br>• ')}`,
      cancelText: 'Cancel',
      actionText: 'OK, I\'ll fill them',
      onConfirm: () => {
        messageModalV1Dismiss();
      }
    });
  }

  return isValid;
}

submitBtn_step1.onclick = function () {
  if (validateStep1()) {
    showSection(step_two_div);
    setActiveStep(2);
  }
};

// ===== Step 2 Validation Function =====
function validateStep2() {
  // Get application type from hidden field
  const applicationTypeField = document.getElementById('applicationType');
  const applicationType = applicationTypeField ? applicationTypeField.value : '';

  // Determine the correct form name based on application type
  const formName = (typeof applicantType !== 'undefined' && applicantType === 'OngoingG12') ? 'Form 138' : 'Form 137';

  const requiredUploads = [
    { fileInputId: 'front_id_file', buttonSelector: 'button[aria-label="upload front id"]', name: 'Applicant Front ID' },
    { fileInputId: 'back_id_file', buttonSelector: 'button[aria-label="upload back id"]', name: 'Applicant Back ID' },
    { fileInputId: 'guardian_front_id_file', buttonSelector: 'button[aria-label="upload guardian front id"]', name: 'Parent/Guardian Front ID' },
    { fileInputId: 'guardian_back_id_file', buttonSelector: 'button[aria-label="upload guardian back id"]', name: 'Parent/Guardian Back ID' },
    { fileInputId: 'birth_certificate_file', buttonSelector: 'button[aria-label="upload birth certificate"]', name: 'Birth Certificate (PSA)' },
    { fileInputId: 'barangay_residence_certificate_file', buttonSelector: 'button[aria-label="upload birth certificate"]', name: 'Barangay Residence Certificate' },
    { fileInputId: 'form_document_file', buttonSelector: 'button[aria-label="upload form document"]', name: formName },
    { fileInputId: 'passport_pictures_file', buttonSelector: 'button[aria-label="upload passport pictures"]', name: 'Two Passport Size Pictures' }
  ];

  let isValid = true;
  let missingFiles = [];

  // Check guardian declaration for conditional affidavit requirement
  const guardianDeclaration = document.getElementById('guardianDeclaration');
  const isGuardianDeclared = guardianDeclaration && guardianDeclaration.checked;

  // Add affidavit to required uploads if guardian is declared
  if (isGuardianDeclared) {
    requiredUploads.push({
      fileInputId: 'affidavit_file',
      buttonSelector: 'button[aria-label="upload affidavit template"]',
      name: 'Notarized Affidavit of Guardianship'
    });
  }

  // Add TOR file to required uploads if applicant is a transferee
  if (applicationType === 'Transferee') {
    requiredUploads.push({
      fileInputId: 'tor_file',
      buttonSelector: '.file_upload_area',
      name: 'TOR (Transcript of Records)'
    });
  }
  
  // Add ALS files to required uploads if applicant is an ALS graduate
  if (applicationType === 'ALSGraduate') {
    requiredUploads.push(
      {
        fileInputId: 'a_e_assesment_file',
        buttonSelector: 'button[aria-label="upload tor file"]',
        name: 'A & E Test Passers (Accreditation and Equivalency A&E Assessment)'
      },
      {
        fileInputId: 'certificate_of_rating_file',
        buttonSelector: 'button[aria-label="upload tor file"]',
        name: 'Certificate of Rating'
      },
      {
        fileInputId: 'certificate_of_completion_file',
        buttonSelector: 'button[aria-label="upload tor file"]',
        name: 'Certificate of Completion (Must Have Citation of "Eligible for College")'
      }
    );
  }

  // Check each required upload
  requiredUploads.forEach(upload => {
    const fileInput = document.getElementById(upload.fileInputId);
    const button = document.querySelector(upload.buttonSelector);

    if (fileInput) {
      let hasValidFiles = false;

      // Special validation for passport pictures (requires exactly 2 files)
      if (upload.fileInputId === 'passport_pictures_file') {
        hasValidFiles = fileInput.files && fileInput.files.length === 2;

        if (!hasValidFiles) {
          isValid = false;
          if (fileInput.files && fileInput.files.length > 0) {
            missingFiles.push(`${upload.name} (exactly 2 files required, ${fileInput.files.length} selected)`);
          } else {
            missingFiles.push(upload.name);
          }
          // Add error styling to the button
          if (button) {
            button.style.borderColor = '#dc2626';
            button.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
          }
        } else {
          // Remove error styling if exactly 2 files are present
          if (button) {
            button.style.borderColor = '';
            button.style.boxShadow = '';
          }
        }
      } else if (upload.fileInputId === 'tor_file') {
        // Special validation for TOR file upload area
        hasValidFiles = fileInput.files && fileInput.files.length > 0;

        if (!hasValidFiles) {
          isValid = false;
          missingFiles.push(upload.name);
          // Add error styling to the file upload area
          if (button) {
            button.style.borderColor = '#dc2626';
            button.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
          }
        } else {
          // Remove error styling if file is present
          if (button) {
            button.style.borderColor = '';
            button.style.boxShadow = '';
          }
        }
      } else {
        // Regular validation for other files (single file)
        hasValidFiles = fileInput.files && fileInput.files.length > 0;

        if (!hasValidFiles) {
          isValid = false;
          missingFiles.push(upload.name);
          // Add error styling to the button
          if (button) {
            button.style.borderColor = '#dc2626';
            button.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
          }
        } else {
          // Remove error styling if file is present
          if (button) {
            button.style.borderColor = '';
            button.style.boxShadow = '';
          }
        }
      }
    }
  });

  if (!isValid) {
    messageModalV1Show({
      icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload-x"><path d="M12 3v12"/><path d="m17 8-5-5-5 5"/><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m9 21 3-3 3 3"/><path d="M12 12l3 3m0-3-3 3"/></svg>`,
      iconBg: '#fef3c7',
      actionBtnBg: '#f59e0b',
      showCancelBtn: false,
      title: 'Required Documents Missing',
      message: `Please upload the following required documents:<br><br>• ${missingFiles.join('<br>• ')}`,
      cancelText: 'Cancel',
      actionText: 'OK, I\'ll upload them',
      onConfirm: () => {
        messageModalV1Dismiss();
      }
    });
  }

  return isValid;
}

submitBtn_step2.onclick = function () {
  if (validateStep2()) {
    showSection(step_3_div);
    setActiveStep(3);
  }
}

// Back buttons
if (backBtn_step2) {
  backBtn_step2.onclick = function () {
    showSection(step_one_div);
    setActiveStep(1);
  }
}

if (backBtn_step3) {
  backBtn_step3.onclick = function () {
    showSection(step_two_div);
    setActiveStep(2);
  }
}

// ===== File Upload Handling =====
function setupFileUploadHandlers() {
  const fileInputs = [
    { id: 'front_id_file', buttonSelector: 'button[aria-label="upload front id"]', placeholderContainer: 'front_id_file' },
    { id: 'back_id_file', buttonSelector: 'button[aria-label="upload back id"]', placeholderContainer: 'back_id_file' },
    { id: 'guardian_front_id_file', buttonSelector: 'button[aria-label="upload guardian front id"]', placeholderContainer: 'guardian_front_id_file' },
    { id: 'guardian_back_id_file', buttonSelector: 'button[aria-label="upload guardian back id"]', placeholderContainer: 'guardian_back_id_file' },
    { id: 'affidavit_file', buttonSelector: 'button[aria-label="upload affidavit template"]', placeholderContainer: 'affidavit_file' },
    { id: 'birth_certificate_file', buttonSelector: 'button[aria-label="upload birth certificate"]', placeholderContainer: 'birth_certificate_file' },
    { id: 'barangay_residence_certificate_file', buttonSelector: 'button[aria-label="upload birth certificate"]', placeholderContainer: 'barangay_residence_certificate_file' },
    { id: 'form_document_file', buttonSelector: 'button[aria-label="upload form document"]', placeholderContainer: 'form_document_file' },
    { id: 'passport_pictures_file', buttonSelector: 'button[aria-label="upload passport pictures"]', placeholderContainer: 'passport_pictures_file' },
    { id: 'tor_file', buttonSelector: 'button[aria-label="upload tor file"]', placeholderContainer: 'tor_file' },
    { id: 'a_e_assesment_file', buttonSelector: 'button[aria-label="upload tor file"]', placeholderContainer: 'a_e_assesment_file' },
    { id: 'certificate_of_rating_file', buttonSelector: 'button[aria-label="upload tor file"]', placeholderContainer: 'certificate_of_rating_file' },
    { id: 'certificate_of_completion_file', buttonSelector: 'button[aria-label="upload tor file"]', placeholderContainer: 'certificate_of_completion_file' }
  ];

  fileInputs.forEach(({ id, buttonSelector, placeholderContainer }) => {
    const fileInput = document.getElementById(id);
    const button = document.querySelector(buttonSelector);
    const viewButton = button ? button.parentElement.querySelector('.view-file-btn') : null;

    if (fileInput && button) {
      fileInput.addEventListener('change', function (e) {
        // Special handling for passport pictures (requires exactly 2 files)
        if (id === 'passport_pictures_file') {
          if (e.target.files && e.target.files.length === 2) {
            // Exactly 2 files selected - add visual feedback
            button.style.backgroundColor = '#22c55e';
            button.style.borderColor = '#22c55e';
            button.style.color = 'white';
            button.classList.add('uploaded');
            // Show view button
            if (viewButton) viewButton.style.display = 'inline-block';
            // Add check icon to title
            updateTitleCheckIcon(id, true);
            // Replace placeholder with uploaded images
            replacePassportPlaceholder(placeholderContainer, e.target.files);
          } else if (e.target.files && e.target.files.length > 0) {
            // Wrong number of files - show warning styling
            button.style.backgroundColor = '#f59e0b';
            button.style.borderColor = '#f59e0b';
            button.style.color = 'white';
            button.classList.remove('uploaded');
            // Show view button (even for wrong count, user can still view)
            if (viewButton) viewButton.style.display = 'inline-block';
            // Remove check icon from title (wrong count)
            updateTitleCheckIcon(id, false);
            // Still show the uploaded images even if count is wrong
            replacePassportPlaceholder(placeholderContainer, e.target.files);
          } else {
            // No files selected - reset styling
            button.style.backgroundColor = '';
            button.style.borderColor = '';
            button.style.color = '';
            button.classList.remove('uploaded');
            // Hide view button
            if (viewButton) viewButton.style.display = 'none';
            // Remove check icon from title
            updateTitleCheckIcon(id, false);
            // Restore placeholder
            restorePassportPlaceholder(placeholderContainer);
          }
        } else {
          // Regular file inputs (single file)
          if (e.target.files && e.target.files.length > 0) {
            // File selected - add visual feedback
            button.style.backgroundColor = '#22c55e';
            button.style.borderColor = '#22c55e';
            button.style.color = 'white';
            button.classList.add('uploaded');

            // Remove any error styling
            button.style.boxShadow = '';
            // Show view button
            if (viewButton) viewButton.style.display = 'inline-block';

            // Add check icon to title
            updateTitleCheckIcon(id, true);

            // Replace placeholder with uploaded file
            replacePlaceholderWithFile(placeholderContainer, e.target.files[0]);
          } else {
            // No file selected - reset styling
            button.style.backgroundColor = '';
            button.style.borderColor = '';
            button.style.color = '';
            button.classList.remove('uploaded');

            // Hide view button
            if (viewButton) viewButton.style.display = 'none';

            // Remove check icon from title
            updateTitleCheckIcon(id, false);

            // Restore placeholder
            restorePlaceholder(placeholderContainer);
          }
        }
      });
    }
  });
}

// Function to update check icons next to section titles
function updateTitleCheckIcon(inputId, showCheck) {
  // Find the title element using a more reliable method
  let titleElement = null;
  const titleTexts = {
    'front_id_file': 'Applicant Front ID',
    'back_id_file': 'Applicant Back ID',
    'guardian_front_id_file': 'Parent/Guardian Front ID',
    'guardian_back_id_file': 'Parent/Guardian Back ID',
    'affidavit_file': 'Notarized Affidavit of Guardianship',
    'birth_certificate_file': 'Birth Certificate (PSA)',
    'form_document_file': ['Form 137', 'Form 138'],
    'passport_pictures_file': 'Two Passport Size Picture White Background With Nameplate',
    'tor_file': 'TOR (Transcript of Records)'
  };

  // Find the title element by searching through all p elements
  const allParagraphs = document.querySelectorAll('p');
  for (let p of allParagraphs) {
    const titleText = titleTexts[inputId];
    if (Array.isArray(titleText)) {
      // For form document, check both Form 137 and Form 138
      if (titleText.some(text => p.textContent.includes(text))) {
        titleElement = p;
        break;
      }
    } else if (p.textContent.includes(titleText)) {
      titleElement = p;
      break;
    }
  }

  if (titleElement) {
    // Remove existing check icon if any
    const existingCheck = titleElement.querySelector('.title-check-icon');
    if (existingCheck) {
      existingCheck.remove();
    }

    if (showCheck) {
      // Add check icon
      const checkIcon = document.createElement('span');
      checkIcon.className = 'title-check-icon';
      checkIcon.innerHTML = ' ✓';
      checkIcon.style.color = '#22c55e';
      checkIcon.style.fontWeight = 'bold';
      checkIcon.style.marginLeft = '8px';

      // Insert before the red asterisk if it exists
      const redAsterisk = titleElement.querySelector('span[style*="color: red"]');
      if (redAsterisk) {
        titleElement.insertBefore(checkIcon, redAsterisk);
      } else {
        titleElement.appendChild(checkIcon);
      }
    }
  }
}

// Helper function to replace placeholder with uploaded file for regular documents
function replacePlaceholderWithFile(inputId, file) {
  // Find the placeholder container based on the input ID
  const placeholderContainer = findPlaceholderContainer(inputId);
  if (!placeholderContainer) return;

  // Check if file is an image
  if (file.type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = function (e) {
      // Store original placeholder for restoration
      if (!placeholderContainer.dataset.originalPlaceholder) {
        placeholderContainer.dataset.originalPlaceholder = placeholderContainer.innerHTML;
      }

      // Replace with uploaded image
      placeholderContainer.innerHTML = `<img src="${e.target.result}" alt="Uploaded file" style="width: 100%; height: 100%; object-fit: contain; border-radius: 8px;">`;
    };
    reader.readAsDataURL(file);
  } else {
    // For non-image files, show file info
    if (!placeholderContainer.dataset.originalPlaceholder) {
      placeholderContainer.dataset.originalPlaceholder = placeholderContainer.innerHTML;
    }

    placeholderContainer.innerHTML = `
      <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; padding: 20px; background: #f8f9fa; border-radius: 8px; border: 2px dashed #22c55e;">
        <div style="font-size: 48px; color: #22c55e; margin-bottom: 10px;">📄</div>
        <div style="font-weight: bold; color: #333; margin-bottom: 5px;">${file.name}</div>
        <div style="font-size: 12px; color: #666;">${(file.size / 1024 / 1024).toFixed(2)} MB</div>
      </div>
    `;
  }
}

// Helper function to replace placeholder with uploaded passport pictures
function replacePassportPlaceholder(inputId, files) {
  const placeholderContainer = findPlaceholderContainer(inputId);
  if (!placeholderContainer) return;

  // Store original placeholder for restoration
  if (!placeholderContainer.dataset.originalPlaceholder) {
    placeholderContainer.dataset.originalPlaceholder = placeholderContainer.innerHTML;
  }

  // Create container for passport photos
  let photosHtml = '<div style="display: flex; gap: 10px; height: 100%; align-items: center; justify-content: center;">';

  Array.from(files).forEach((file, index) => {
    if (file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = function (e) {
        const photoElement = document.createElement('div');
        photoElement.style.cssText = 'flex: 1; height: 100%; display: flex; align-items: center; justify-content: center;';
        photoElement.innerHTML = `<img src="${e.target.result}" alt="Passport photo ${index + 1}" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 4px; border: 2px solid #22c55e;">`;

        if (index === 0) {
          placeholderContainer.innerHTML = '';
          placeholderContainer.appendChild(photoElement);
        } else {
          placeholderContainer.appendChild(photoElement);
        }
      };
      reader.readAsDataURL(file);
    }
  });
}

// Helper function to restore original placeholder for regular documents
function restorePlaceholder(inputId) {
  const placeholderContainer = findPlaceholderContainer(inputId);
  if (!placeholderContainer) return;

  if (placeholderContainer.dataset.originalPlaceholder) {
    placeholderContainer.innerHTML = placeholderContainer.dataset.originalPlaceholder;
    delete placeholderContainer.dataset.originalPlaceholder;
  }
}

// Helper function to restore original placeholder for passport pictures
function restorePassportPlaceholder(inputId) {
  const placeholderContainer = findPlaceholderContainer(inputId);
  if (!placeholderContainer) return;

  if (placeholderContainer.dataset.originalPlaceholder) {
    placeholderContainer.innerHTML = placeholderContainer.dataset.originalPlaceholder;
    delete placeholderContainer.dataset.originalPlaceholder;
  }
}

// Helper function to find placeholder container based on input ID
function findPlaceholderContainer(inputId) {
  // Map input IDs to their corresponding placeholder containers
  const containerMap = {
    'front_id_file': 'front_id_file',
    'back_id_file': 'back_id_file',
    'guardian_front_id_file': 'guardian_front_id_file',
    'guardian_back_id_file': 'guardian_back_id_file',
    'affidavit_file': 'affidavit_file',
    'birth_certificate_file': 'birth_certificate_file',
    'form_document_file': 'form_document_file',
    'passport_pictures_file': 'passport_pictures_file',
    'tor_file': 'tor_file'
  };

  const containerId = containerMap[inputId];
  if (!containerId) return null;

  // Find the file input element and then find its associated placeholder container
  const fileInput = document.getElementById(containerId);
  if (!fileInput) return null;

  // Find the closest upload section container (try both container types)
  let uploadSection = fileInput.closest('.back_id__card_container_upper');
  if (!uploadSection) {
    uploadSection = fileInput.closest('.front_id__card_container_upper');
  }
  if (!uploadSection) return null;

  // Find the officers__card-picture div within this section
  return uploadSection.querySelector('.officers__card-picture');
}

// Initialize file upload handlers when DOM is loaded
document.addEventListener('DOMContentLoaded', setupFileUploadHandlers);


// ===== Guardian Declaration Functionality =====
const guardianDeclaration = document.getElementById('guardianDeclaration');
const affidavitSection = document.getElementById('affidavitSection');

function toggleAffidavitSection() {
  if (guardianDeclaration && affidavitSection) {
    if (guardianDeclaration.checked) {
      affidavitSection.style.display = 'flex';
      // Add smooth animation
      affidavitSection.classList.add('fade-in');
    } else {
      affidavitSection.style.display = 'none';
      affidavitSection.classList.remove('fade-in');
    }
  }
}

// Add event listener for guardian declaration checkbox
if (guardianDeclaration) {
  guardianDeclaration.addEventListener('change', toggleAffidavitSection);
}

// ======================= STRAND AND PROGRAM HANDLING =======================

// Get references to the dropdowns
const strandTrackDropdown = document.getElementById('strand_track');
const firstChoiceProgramDropdown = document.getElementById('first_choice_program');
const secondChoiceProgramDropdown = document.getElementById('second_choice_program');

// Function to disable/enable program dropdowns
function toggleProgramDropdowns(enabled) {
  if (firstChoiceProgramDropdown) {
    firstChoiceProgramDropdown.disabled = !enabled;
    if (!enabled) {
      firstChoiceProgramDropdown.value = '';
      // Clear options except the first placeholder
      firstChoiceProgramDropdown.innerHTML = '<option value="" disabled selected hidden></option>';
    }
  }

  if (secondChoiceProgramDropdown) {
    secondChoiceProgramDropdown.disabled = !enabled;
    if (!enabled) {
      secondChoiceProgramDropdown.value = '';
      // Clear options except the first placeholder
      secondChoiceProgramDropdown.innerHTML = '<option value="" disabled selected hidden></option>';
    }
  }
}

// Function to populate program dropdowns
function populateProgramDropdowns(programs) {
  // Clear existing options and add placeholder
  const placeholderOption = '<option value="" disabled selected hidden></option>';

  if (firstChoiceProgramDropdown) {
    firstChoiceProgramDropdown.innerHTML = placeholderOption;
    programs.forEach(program => {
      const option = document.createElement('option');
      option.value = program.name;
      option.textContent = program.name;
      firstChoiceProgramDropdown.appendChild(option);
    });
  }

  if (secondChoiceProgramDropdown) {
    secondChoiceProgramDropdown.innerHTML = placeholderOption;
    programs.forEach(program => {
      const option = document.createElement('option');
      option.value = program.name;
      option.textContent = program.name;
      secondChoiceProgramDropdown.appendChild(option);
    });
  }
}

// Function to fetch programs based on selected strand
async function fetchProgramsByStrand(strandName) {
  try {
    const response = await fetch(`../api/fetch-programs?strand=${encodeURIComponent(strandName)}`);
    const data = await response.json();

    if (data.success) {
      return data.data;
    } else {
      console.error('Error fetching programs:', data.message);
      messageModalV1Show(
        'warning',
        '#f59e0b',
        '#fef3c7',
        'Error Loading Programs',
        'warning',
        'Unable to load programs for the selected strand. Please try again.',
        'Retry'
      );
      return [];
    }
  } catch (error) {
    console.error('Network error:', error);
    messageModalV1Show(
      'warning',
      '#f59e0b',
      '#fef3c7',
      'Network Error',
      'warning',
      'Unable to connect to the server. Please check your connection and try again.',
      'OK'
    );
    return [];
  }
}

// Function to fetch all programs (for transferees)
async function fetchAllPrograms() {
  try {
    const response = await fetch('../api/fetch-all-programs');
    const data = await response.json();

    if (data.success) {
      return data.data;
    } else {
      console.error('Error fetching all programs:', data.message);
      messageModalV1Show(
        'warning',
        '#f59e0b',
        '#fef3c7',
        'Error Loading Programs',
        'warning',
        'Unable to load all programs. Please try again.',
        'Retry'
      );
      return [];
    }
  } catch (error) {
    console.error('Network error:', error);
    messageModalV1Show(
      'warning',
      '#f59e0b',
      '#fef3c7',
      'Network Error',
      'warning',
      'Unable to connect to the server. Please check your connection and try again.',
      'OK'
    );
    return [];
  }
}

// Program choice validation removed - users can now select the same program for both choices

// Event listener for strand selection
if (strandTrackDropdown) {
  strandTrackDropdown.addEventListener('change', async function () {
    const selectedStrand = this.value;

    if (selectedStrand) {
      // Check if this is a transferee application
      const applicationTypeField = document.getElementById('applicationType');
      const applicationType = applicationTypeField ? applicationTypeField.value : '';

      // Enable program dropdowns and fetch programs
      toggleProgramDropdowns(true);

      if (applicationType === 'Transferee') {
        // For transferees, load all available programs regardless of strand
        const programs = await fetchAllPrograms();
        populateProgramDropdowns(programs);
      } else {
        // For non-transferees, load programs based on selected strand
        const programs = await fetchProgramsByStrand(selectedStrand);
        populateProgramDropdowns(programs);
      }
    } else {
      // Disable program dropdowns if no strand selected
      toggleProgramDropdowns(false);
    }
  });
}

// Program choice validation event listeners removed - no longer preventing duplicate selections

// Initialize: disable program dropdowns on page load, but enable for transferees
document.addEventListener('DOMContentLoaded', async function () {
  const applicationTypeField = document.getElementById('applicationType');
  const applicationType = applicationTypeField ? applicationTypeField.value : '';

  if (applicationType === 'Transferee') {
    // For transferees, enable program dropdowns and load all programs immediately
    toggleProgramDropdowns(true);
    const programs = await fetchAllPrograms();
    populateProgramDropdowns(programs);
  } else {
    // For non-transferees, keep dropdowns disabled until strand is selected
    toggleProgramDropdowns(false);
  }
});

// ======================= END STRAND AND PROGRAM HANDLING =======================

// Ensure initial state
try { setActiveStep(1); } catch (e) { }
// Ensure initial visibility
if (step_one_div) { step_one_div.style.display = step_one_div.style.display || 'block'; }

// Function to view uploaded files
function viewFile(inputId) {
  const fileInput = document.getElementById(inputId);

  if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
    alert('No file selected to view.');
    return;
  }

  // Special handling for passport pictures (multiple files in one window)
  if (inputId === 'passport_pictures_file' && fileInput.files.length > 1) {
    viewPassportPictures(fileInput.files);
  } else {
    // Single file
    const file = fileInput.files[0];
    viewSingleFile(file, inputId);
  }
}

// Function to view passport pictures (multiple files in one window)
function viewPassportPictures(files) {
  const newWindow = window.open('', '_blank');

  let imagesHTML = '';
  const fileURLs = [];

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    const fileURL = URL.createObjectURL(file);
    fileURLs.push(fileURL);
    imagesHTML += `<img src="${fileURL}" alt="Passport Picture ${i + 1}" />`;
  }

  newWindow.document.write(`
        <html>
            <head>
                <title>Passport Pictures</title>
                <style>
                    body { 
                        margin: 0; 
                        padding: 20px; 
                        background: #f5f5f5; 
                        display: flex; 
                        justify-content: center; 
                        align-items: center; 
                        min-height: 100vh;
                        flex-direction: column;
                    }
                    .pictures-container {
                        display: flex;
                        gap: 20px;
                        flex-wrap: wrap;
                        justify-content: center;
                        align-items: center;
                    }
                    img { 
                        max-width: 45%; 
                        max-height: 80vh; 
                        box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
                        border-radius: 8px;
                        object-fit: contain;
                    }
                    h2 {
                        color: #333;
                        text-align: center;
                        margin-bottom: 20px;
                        font-family: Arial, sans-serif;
                    }
                    @media (max-width: 768px) {
                        img { max-width: 90%; }
                        .pictures-container { flex-direction: column; }
                    }
                </style>
            </head>
            <body>
                <h2>Passport Pictures</h2>
                <div class="pictures-container">
                    ${imagesHTML}
                </div>
            </body>
        </html>
    `);

  // Clean up the object URLs after a delay
  setTimeout(() => {
    fileURLs.forEach(url => URL.revokeObjectURL(url));
  }, 1000);
}

// Function to view a single file
function viewSingleFile(file, title) {
  const fileURL = URL.createObjectURL(file);
  const fileType = file.type;

  // Create a new window/tab to display the file
  const newWindow = window.open('', '_blank');

  if (fileType.startsWith('image/')) {
    // Display image
    newWindow.document.write(`
            <html>
                <head>
                    <title>${title}</title>
                    <style>
                        body { 
                            margin: 0; 
                            padding: 20px; 
                            background: #f5f5f5; 
                            display: flex; 
                            justify-content: center; 
                            align-items: center; 
                            min-height: 100vh; 
                        }
                        img { 
                            max-width: 100%; 
                            max-height: 100vh; 
                            box-shadow: 0 4px 8px rgba(0,0,0,0.1); 
                            border-radius: 8px; 
                        }
                    </style>
                </head>
                <body>
                    <img src="${fileURL}" alt="${title}" />
                </body>
            </html>
        `);
  } else if (fileType === 'application/pdf') {
    // Display PDF
    newWindow.document.write(`
            <html>
                <head>
                    <title>${title}</title>
                    <style>
                        body { margin: 0; padding: 0; }
                        iframe { width: 100%; height: 100vh; border: none; }
                    </style>
                </head>
                <body>
                    <iframe src="${fileURL}" type="application/pdf"></iframe>
                </body>
            </html>
        `);
  } else {
    // For other file types, try to display or download
    newWindow.location.href = fileURL;
  }

  // Clean up the object URL after a delay to allow the browser to load it
  setTimeout(() => {
    URL.revokeObjectURL(fileURL);
  }, 1000);
}

// ======================= FORM SUBMISSION LOGIC =======================

// Function to collect all form data
function collectFormData() {
  const formData = {};

  // Application Type (if available)
  const applicationType = document.getElementById('applicationType');
  if (applicationType) formData.application_type = applicationType.value;

  // Personal Information
  formData.first_name = document.getElementById('firstName')?.value || '';
  formData.middle_name = document.getElementById('middleName')?.value || '';
  formData.last_name = document.getElementById('lastName')?.value || '';
  formData.suffix = document.getElementById('suffix')?.value || '';
  formData.gender = document.getElementById('gender')?.value || '';
  formData.birth_date = document.getElementById('birthday')?.value || '';
  formData.contact_number = document.getElementById('contactNumber')?.value || '';

  // Address Information
  formData.house_number = document.getElementById('houseNumber')?.value || '';
  formData.street_name = document.getElementById('streetName')?.value || '';
  formData.subdivision = document.getElementById('subdivision')?.value || '';
  formData.city = document.getElementById('city')?.value || '';
  formData.barangay = document.getElementById('barangay')?.value || '';

  // Document Files (file names/paths)
  formData.front_id_file = document.getElementById('front_id_file')?.files[0]?.name || '';
  formData.back_id_file = document.getElementById('back_id_file')?.files[0]?.name || '';
  formData.guardian_front_id_file = document.getElementById('guardian_front_id_file')?.files[0]?.name || '';
  formData.guardian_back_id_file = document.getElementById('guardian_back_id_file')?.files[0]?.name || '';
  formData.affidavit_file = document.getElementById('affidavit_file')?.files[0]?.name || '';
  formData.guardian_declaration = document.getElementById('guardianDeclaration')?.value || '';
  formData.birth_certificate_file = document.getElementById('birth_certificate_file')?.files[0]?.name || '';
  formData.barangay_residence_certificate_file = document.getElementById('barangay_residence_certificate_file')?.files[0]?.name || '';
  formData.form_document_file = document.getElementById('form_document_file')?.files[0]?.name || '';
  formData.passport_pictures_file = document.getElementById('passport_pictures_file')?.files[0]?.name || '';

  // Academic Information
  formData.lrn = document.getElementById('lrn')?.value || '';
  formData.last_school_attended = document.getElementById('lastSchoolAttended')?.value || '';
  formData.type_of_school = document.getElementById('type_of_school')?.value || '';
  formData.strand_track = document.getElementById('strand_track')?.value || '';

  // Academic Averages
  formData.general_average = document.getElementById('avg_fil')?.value || '';
  formData.filipino_average = document.getElementById('avg_fil')?.value || '';
  formData.english_average = document.getElementById('avg_eng')?.value || '';
  formData.mathematics_average = document.getElementById('avg_math')?.value || '';
  formData.science_average = document.getElementById('avg_scie')?.value || '';
  formData.overall_average = document.getElementById('overall_avg')?.value || '';

  // Transferee-specific fields
  formData.previous_year_level = document.getElementById('prev_year')?.value || '';
  formData.previous_course = document.getElementById('prev_course')?.value || '';
  formData.tor_file = document.getElementById('tor_file')?.files[0]?.name || '';

  // ALS Graduate-specific files
  formData.a_e_assesment_file = document.getElementById('a_e_assesment_file')?.files[0]?.name || '';
  formData.certificate_of_rating_file = document.getElementById('certificate_of_rating_file')?.files[0]?.name || '';
  formData.certificate_of_completion_file = document.getElementById('certificate_of_completion_file')?.files[0]?.name || '';

  // Program Choices
  formData.first_choice_program = document.getElementById('first_choice_program')?.value || '';
  formData.second_choice_program = document.getElementById('second_choice_program')?.value || '';

  return formData;
}

// Function to validate step 3 (final validation)
function validateStep3() {
  const errors = [];

  // Clear previous error styles for program fields
  const firstChoiceElement = document.getElementById('first_choice_program');
  const secondChoiceElement = document.getElementById('second_choice_program');
  
  if (firstChoiceElement) {
    firstChoiceElement.style.borderColor = '';
    firstChoiceElement.style.boxShadow = '';
  }
  if (secondChoiceElement) {
    secondChoiceElement.style.borderColor = '';
    secondChoiceElement.style.boxShadow = '';
  }

  // Get application type to determine validation requirements
  const applicationType = document.querySelector('input[name="application_type"]:checked')?.value;
  const isTransferee = applicationType === 'Transferee';

  // Check if LRN is provided
  const lrn = document.getElementById('lrn')?.value;
  if (!lrn || lrn.trim() === '') {
    errors.push('Learner Reference Number (LRN) is required');
  }

  // Check if last school attended is provided
  const lastSchool = document.getElementById('lastSchoolAttended')?.value;
  if (!lastSchool || lastSchool.trim() === '') {
    errors.push('Last School Attended is required');
  }

  // Check if type of school is selected
  const typeOfSchool = document.getElementById('type_of_school')?.value;
  if (!typeOfSchool || typeOfSchool === '') {
    errors.push('Type of School is required');
  }

  // Check if strand/track is selected (only for non-transferees)
  if (!isTransferee) {
    const strandTrack = document.getElementById('strand_track')?.value;
    if (!strandTrack || strandTrack === '') {
      errors.push('Strand/Track is required');
    }
  }

  // Check if first choice program is selected
  const firstChoice = document.getElementById('first_choice_program')?.value;
  if (!firstChoice || firstChoice === '') {
    errors.push('First Choice Program is required');
    // Add error styling
    if (firstChoiceElement) {
      firstChoiceElement.style.borderColor = '#dc2626';
      firstChoiceElement.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
    }
  }

  // Check if second choice program is selected
  const secondChoice = document.getElementById('second_choice_program')?.value;
  if (!secondChoice || secondChoice === '') {
    errors.push('Second Choice Program is required');
    // Add error styling
    if (secondChoiceElement) {
      secondChoiceElement.style.borderColor = '#dc2626';
      secondChoiceElement.style.boxShadow = '0 0 0 3px rgba(220, 38, 38, 0.1)';
    }
  }

  if (isTransferee) {
    // Validate transferee-specific fields
    const prevYear = document.getElementById('prev_year')?.value;
    const prevCourse = document.getElementById('prev_course')?.value;

    if (!prevYear || prevYear.trim() === '') {
      errors.push('Previous Year Level is required');
    }
    if (!prevCourse || prevCourse.trim() === '') {
      errors.push('Previous Course is required');
    }
  } else {
    // Check academic averages (only for non-transferees)
    const avgFil = document.getElementById('avg_fil')?.value;
    const avgEng = document.getElementById('avg_eng')?.value;
    const avgMath = document.getElementById('avg_math')?.value;
    const avgScie = document.getElementById('avg_scie')?.value;
    const overallAvg = document.getElementById('overall_avg')?.value;

    if (!avgFil || avgFil.trim() === '') errors.push('Filipino average is required');
    if (!avgEng || avgEng.trim() === '') errors.push('English average is required');
    if (!avgMath || avgMath.trim() === '') errors.push('Math average is required');
    if (!avgScie || avgScie.trim() === '') errors.push('Science average is required');
    if (!overallAvg || overallAvg.trim() === '') errors.push('Overall average is required');
  }

  return errors;
}

// Function to submit the application
async function submitApplication() {
  try {
    // Show loading state on review modal submit button
    const reviewSubmitButton = document.querySelector('.review-submit-btn');
    const originalText = reviewSubmitButton ? reviewSubmitButton.textContent : 'Submit Application';
    if (reviewSubmitButton) {
      reviewSubmitButton.textContent = 'Submitting...';
      reviewSubmitButton.disabled = true;
    }

    // Collect all form data
    const formData = collectFormData();

    // Submit to API
    const response = await fetch('../api/submit_application_admission.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData)
    });

    const result = await response.json();

    if (result.success) {
      // Close the review modal
      closeReviewModal();

      // Show success message
      messageModalV1Show(
        'success',
        '#10b981',
        '#d1fae5',
        'Application Submitted Successfully!',
        'success',
        `Your application has been submitted successfully. Application ID: ${result.application_id}`,
        'OK'
      );

      // Optionally redirect or reset form
      setTimeout(() => {
        window.location.href = 'my_application.php';
      }, 2000);

    } else {
      // Show error message
      messageModalV1Show(
        'error',
        '#ef4444',
        '#fee2e2',
        'Submission Failed',
        'error',
        result.message || 'Failed to submit application. Please try again.',
        'OK'
      );
    }

  } catch (error) {
    console.error('Submission error:', error);
    messageModalV1Show(
      'error',
      '#ef4444',
      '#fee2e2',
      'Network Error',
      'error',
      'Unable to submit application. Please check your connection and try again.',
      'Retry'
    );
  } finally {
    // Restore button state
    const reviewSubmitButton = document.querySelector('.review-submit-btn');
    if (reviewSubmitButton) {
      reviewSubmitButton.textContent = originalText;
      reviewSubmitButton.disabled = false;
    }
  }
}

// Add event listener for step 3 submit button (now shows review modal)
if (submitBtn_step3) {
  submitBtn_step3.onclick = function () {
    // Validate step 3
    const errors = validateStep3();

    if (errors.length > 0) {
      messageModalV1Show({
        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="m12 17 .01 0"/></svg>`,
        iconBg: '#fef3c7',
        actionBtnBg: '#f59e0b',
        showCancelBtn: false,
        title: 'Validation Error',
        message: 'Please fix the following errors:<br>• ' + errors.join('<br>• '),
        cancelText: 'Cancel',
        actionText: 'OK'
      });
      return;
    }

    // If validation passes, show the review modal
    showReviewModal();
  };
}

// ===== TEST DATA POPULATION FUNCTION =====
// Call this function to populate form fields with test data for easy testing
// To remove test data, simply comment out or delete the function call
function populateTestData() {
  // Personal Information
  document.getElementById('firstName').value = 'Juan';
  document.getElementById('middleName').value = 'Santos';
  document.getElementById('lastName').value = 'Dela Cruz';
  document.getElementById('suffix').value = 'Jr.';
  document.getElementById('gender').value = 'male';
  document.getElementById('birthday').value = '2000-01-15';
  document.getElementById('contactNumber').value = '09123456789';

  // Address Information
  document.getElementById('houseNumber').value = '123';
  document.getElementById('streetName').value = 'Main Street';
  document.getElementById('subdivision').value = 'Greenfield Village';
  document.getElementById('city').value = 'Quezon City';
  document.getElementById('barangay').value = 'Barangay 1';

  // Academic Information
  document.getElementById('lrn').value = '123456789012';
  document.getElementById('lastSchoolAttended').value = 'Sample High School';
  document.getElementById('type_of_school').value = 'Public';
  document.getElementById('strand_track').value = 'STEM';

  // Academic Averages (for non-transferees)
  document.getElementById('avg_fil').value = '90';
  document.getElementById('avg_eng').value = '88';
  document.getElementById('avg_math').value = '92';
  document.getElementById('avg_scie').value = '89';
  document.getElementById('overall_avg').value = '90';

  // Transferee-specific test data
  const prevYearField = document.getElementById('prev_year');
  const prevCourseField = document.getElementById('prev_course');
  if (prevYearField) prevYearField.value = 'Second Year';
  if (prevCourseField) prevCourseField.value = 'Bachelor of Science in Computer Science';

  // Note: Removed createPlaceholderFiles() to keep original sample images visible
  // If you need to test file uploads, manually upload files instead

  console.log('Test data populated successfully!');
}

// Function to create placeholder files for testing
function createPlaceholderFiles() {
  // Create a simple 1x1 pixel transparent PNG as placeholder
  const canvas = document.createElement('canvas');
  canvas.width = 1;
  canvas.height = 1;
  const ctx = canvas.getContext('2d');
  ctx.fillStyle = 'rgba(0,0,0,0)';
  ctx.fillRect(0, 0, 1, 1);

  // Convert canvas to blob and create file objects
  canvas.toBlob(function (blob) {
    // File input IDs and their corresponding placeholder names
    const fileInputs = [
      { id: 'front_id_file', name: 'front_id_placeholder.png' },
      { id: 'back_id_file', name: 'back_id_placeholder.png' },
      { id: 'guardian_front_id_file', name: 'guardian_front_id_placeholder.png' },
      { id: 'guardian_back_id_file', name: 'guardian_back_id_placeholder.png' },
      { id: 'affidavit_file', name: 'affidavit_placeholder.png' },
      { id: 'birth_certificate_file', name: 'birth_certificate_placeholder.png' },
      { id: 'form_document_file', name: 'form_document_placeholder.png' },
      { id: 'tor_file', name: 'tor_placeholder.png' }
    ];

    // Create placeholder files for single file inputs
    fileInputs.forEach(input => {
      const fileInput = document.getElementById(input.id);
      if (fileInput) {
        const file = new File([blob], input.name, { type: 'image/png' });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;

        // Trigger change event to update UI
        fileInput.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });

    // Special handling for passport pictures (requires 2 files)
    const passportInput = document.getElementById('passport_pictures_file');
    if (passportInput) {
      const file1 = new File([blob], 'passport_picture_1.png', { type: 'image/png' });
      const file2 = new File([blob], 'passport_picture_2.png', { type: 'image/png' });
      const dataTransfer = new DataTransfer();
      dataTransfer.items.add(file1);
      dataTransfer.items.add(file2);
      passportInput.files = dataTransfer.files;

      // Trigger change event to update UI
      passportInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    console.log('Placeholder files created for all required documents!');
  }, 'image/png');
}

// Function to handle conditional form display based on application type
function handleConditionalFormDisplay() {
  const applicationType = document.getElementById('applicationType');
  const academicAveragesContainer = document.getElementById('academic_averages_container');
  const transfereeFieldsContainer = document.getElementById('transferee_fields');
  const torUploadSection = document.getElementById('tor_upload_section');
  const strandTrackDropdown = document.getElementById('strand_track');
  
  // ALS Graduate specific elements
  const alsAssessmentSection = document.getElementById('a_e_assesment_upload_section');
  const alsCertificateRatingSection = document.getElementById('certificate_of_rating_upload_section');
  const alsCertificateCompletionSection = document.getElementById('certificate_of_completion_upload_section');

  if (!applicationType || !academicAveragesContainer || !transfereeFieldsContainer) {
    console.log('Required elements not found for conditional display');
    return;
  }

  const appType = applicationType.value;
  console.log('Application type:', appType);

  if (appType === 'ALSGraduate') {
    // Hide academic averages for ALS graduates
    academicAveragesContainer.style.display = 'none';
    
    // Hide transferee-specific fields
    transfereeFieldsContainer.style.display = 'none';
    
    // Hide TOR upload section
    if (torUploadSection) {
      torUploadSection.style.display = 'none';
    }
    
    // Show ALS-specific upload sections
    if (alsAssessmentSection) alsAssessmentSection.style.display = 'block';
    if (alsCertificateRatingSection) alsCertificateRatingSection.style.display = 'block';
    if (alsCertificateCompletionSection) alsCertificateCompletionSection.style.display = 'block';
    
    // Show strand track dropdown for ALS graduates
    if (strandTrackDropdown) {
      const strandTrackContainer = strandTrackDropdown.closest('.input_group');
      if (strandTrackContainer) {
        strandTrackContainer.style.display = 'block';
      }
    }
    
    // Remove required attribute from academic average fields
    const academicFields = ['avg_fil', 'avg_eng', 'avg_math', 'avg_scie', 'overall_avg'];
    academicFields.forEach(fieldId => {
      const field = document.getElementById(fieldId);
      if (field) {
        field.removeAttribute('required');
        field.closest('.input_group')?.querySelector('.form_label')?.classList.remove('required');
      }
    });
    
    // Remove required attribute from transferee fields
    const transfereeFields = ['prev_year', 'prev_course', 'tor_file'];
    transfereeFields.forEach(fieldId => {
      const field = document.getElementById(fieldId);
      if (field) {
        field.removeAttribute('required');
        field.closest('.input_group')?.querySelector('.form_label')?.classList.remove('required');
      }
    });
    
    // Add required attribute to ALS fields
    const alsFields = ['a_e_assesment_file', 'certificate_of_rating_file', 'certificate_of_completion_file'];
    alsFields.forEach(fieldId => {
      const field = document.getElementById(fieldId);
      if (field) {
        field.setAttribute('required', 'required');
      }
    });
    
    console.log('ALS Graduate mode: Academic averages hidden, ALS upload sections shown');
  } else if (appType === 'Transferee') {
    // Hide academic averages for transferees
    academicAveragesContainer.style.display = 'none';

    // Show transferee-specific fields
    transfereeFieldsContainer.style.display = 'block';

    // Show TOR upload section in Step 2
    if (torUploadSection) {
      torUploadSection.style.display = 'block';
    }

    // Hide strand track dropdown for transferees
    if (strandTrackDropdown) {
      const strandTrackContainer = strandTrackDropdown.closest('.input_group');
      if (strandTrackContainer) {
        strandTrackContainer.style.display = 'none';
      }
    }

    // Remove required attribute from academic average fields
    const academicFields = ['avg_fil', 'avg_eng', 'avg_math', 'avg_scie', 'overall_avg'];
    academicFields.forEach(fieldId => {
      const field = document.getElementById(fieldId);
      if (field) {
        field.removeAttribute('required');
        field.closest('.input_group')?.querySelector('.form_label')?.classList.remove('required');
      }
    });

    // Add required attribute to transferee fields
    const transfereeFields = ['prev_year', 'prev_course', 'tor_file'];
    transfereeFields.forEach(fieldId => {
      const field = document.getElementById(fieldId);
      if (field) {
        field.setAttribute('required', 'required');
        field.closest('.input_group')?.querySelector('.form_label')?.classList.add('required');
      }
    });

    console.log('Transferee mode: Academic averages hidden, transferee fields shown, TOR upload visible');
  } else {
    // Show academic averages for non-transferees and non-ALS graduates
    academicAveragesContainer.style.display = 'block';

    // Hide transferee-specific fields
    transfereeFieldsContainer.style.display = 'none';

    // Hide TOR upload section in Step 2
    if (torUploadSection) {
      torUploadSection.style.display = 'none';
    }
    
    // Hide ALS-specific upload sections
    if (alsAssessmentSection) alsAssessmentSection.style.display = 'none';
    if (alsCertificateRatingSection) alsCertificateRatingSection.style.display = 'none';
    if (alsCertificateCompletionSection) alsCertificateCompletionSection.style.display = 'none';

    // Show strand track dropdown for non-transferees
    if (strandTrackDropdown) {
      const strandTrackContainer = strandTrackDropdown.closest('.input_group');
      if (strandTrackContainer) {
        strandTrackContainer.style.display = 'block';
      }
    }

    // Add required attribute to academic average fields
    const academicFields = ['avg_fil', 'avg_eng', 'avg_math', 'avg_scie', 'overall_avg'];
    academicFields.forEach(fieldId => {
      const field = document.getElementById(fieldId);
      if (field) {
        field.setAttribute('required', 'required');
        field.closest('.input_group')?.querySelector('.form_label')?.classList.add('required');
      }
    });

    // Remove required attribute from transferee fields
    const transfereeFields = ['prev_year', 'prev_course', 'tor_file'];
    transfereeFields.forEach(fieldId => {
      const field = document.getElementById(fieldId);
      if (field) {
        field.removeAttribute('required');
        field.closest('.input_group')?.querySelector('.form_label')?.classList.remove('required');
      }
    });
    
    // Remove required attribute from ALS fields
    const alsFields = ['a_e_assesment_file', 'certificate_of_rating_file', 'certificate_of_completion_file'];
    alsFields.forEach(fieldId => {
      const field = document.getElementById(fieldId);
      if (field) {
        field.removeAttribute('required');
      }
    });

    console.log('Regular mode: Academic averages shown, transferee and ALS fields hidden');
  }
}

// Initialize conditional form display when page loads
document.addEventListener('DOMContentLoaded', function () {
  handleConditionalFormDisplay();
});

// Uncomment the line below to automatically populate test data when page loads
populateTestData();

// Review Modal Functions
function showReviewModal() {
  const modal = document.getElementById('reviewModal');
  if (modal) {
    populateReviewModal();
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
  }
}

function closeReviewModal() {
  const modal = document.getElementById('reviewModal');
  if (modal) {
    modal.style.display = 'none';
    document.body.style.overflow = 'auto'; // Restore scrolling
  }
}

function populateReviewModal() {
  // Get form data
  const formData = collectFormData();

  // Populate personal information
  const fullName = `${formData.firstName || ''} ${formData.middleName || ''} ${formData.lastName || ''} ${formData.suffix || ''}`.trim();
  document.getElementById('review-fullname').textContent = fullName;
  document.getElementById('review-gender').textContent = formData.gender || 'Not specified';
  document.getElementById('review-birthday').textContent = formData.birthday || 'Not specified';
  document.getElementById('review-contact').textContent = formData.contactNumber || 'Not specified';

  // Populate address information
  const address = `${formData.houseNumber || ''} ${formData.streetName || ''}, ${formData.subdivision || ''}, ${formData.city || ''}, ${formData.barangay || ''}`.trim();
  document.getElementById('review-address').textContent = address;

  // Populate academic information
  document.getElementById('review-lrn').textContent = formData.lrn || 'Not specified';
  document.getElementById('review-school').textContent = formData.lastSchoolAttended || 'Not specified';
  document.getElementById('review-school-type').textContent = formData.type_of_school || 'Not specified';
  document.getElementById('review-strand').textContent = formData.strand_track || 'Not specified';

  // Handle conditional sections based on application type
  const applicationType = document.getElementById('applicationType').value;;
  const isTransferee = applicationType === 'Transferee';

  // Show/hide sections based on application type
  const averagesSection = document.getElementById('review-averages-section');
  const transfereeSection = document.getElementById('review-transferee-section');
  const strandContainer = document.getElementById('review-strand-container');

  if (isTransferee) {
    // Hide averages and strand for transferees
    if (averagesSection) averagesSection.style.display = 'none';
    if (strandContainer) strandContainer.style.display = 'none';
    if (transfereeSection) transfereeSection.style.display = 'block';

    // Populate transferee information
    document.getElementById('review-prev-year').textContent = formData.prev_year || 'Not specified';
    document.getElementById('review-prev-course').textContent = formData.prev_course || 'Not specified';
  } else {
    // Show averages and strand for non-transferees
    if (averagesSection) averagesSection.style.display = 'block';
    if (strandContainer) strandContainer.style.display = 'block';
    if (transfereeSection) transfereeSection.style.display = 'none';

    // Populate academic averages
    document.getElementById('review-avg-fil').textContent = formData.avg_fil || 'Not specified';
    document.getElementById('review-avg-eng').textContent = formData.avg_eng || 'Not specified';
    document.getElementById('review-avg-math').textContent = formData.avg_math || 'Not specified';
    document.getElementById('review-avg-scie').textContent = formData.avg_scie || 'Not specified';
    document.getElementById('review-overall-avg').textContent = formData.overall_avg || 'Not specified';
  }

  // Populate program choices
  document.getElementById('review-first-program').textContent = formData.first_choice_program || 'Not selected';
  document.getElementById('review-second-program').textContent = formData.second_choice_program || 'Not selected';

  // Populate document status
  populateDocumentStatus();

  // Update form document label based on application type
  const formDocLabel = document.getElementById('review-form-doc-label');
  if (formDocLabel) {
    formDocLabel.textContent = isTransferee ? 'Form 138:' : 'Form 137:';
  }

  // Show/hide TOR for transferees
  const torItem = document.getElementById('review-tor-item');
  if (torItem) {
    torItem.style.display = isTransferee ? 'block' : 'none';
  }

  // Show/hide affidavit based on guardian declaration
  const guardianDeclaration = document.getElementById('guardianDeclaration');
  const affidavitItem = document.getElementById('review-affidavit-item');
  if (affidavitItem && guardianDeclaration) {
    affidavitItem.style.display = guardianDeclaration.checked ? 'block' : 'none';
  }
}

function populateDocumentStatus() {
  const documentFields = [
    { id: 'front_id_file', reviewId: 'review-front-id' },
    { id: 'back_id_file', reviewId: 'review-back-id' },
    { id: 'guardian_front_id_file', reviewId: 'review-guardian-front-id' },
    { id: 'guardian_back_id_file', reviewId: 'review-guardian-back-id' },
    { id: 'affidavit_file', reviewId: 'review-affidavit' },
    { id: 'birth_certificate_file', reviewId: 'review-birth-cert' },
    { id: 'form_document_file', reviewId: 'review-form-doc' },
    { id: 'passport_pictures_file', reviewId: 'review-passport-pics' },
    { id: 'tor_file', reviewId: 'review-tor' }
  ];

  documentFields.forEach(field => {
    const fileInput = document.getElementById(field.id);
    const reviewElement = document.getElementById(field.reviewId);

    if (fileInput && reviewElement) {
      const hasFile = fileInput.files && fileInput.files.length > 0;
      reviewElement.textContent = hasFile ? 'Uploaded' : 'Not uploaded';
      reviewElement.className = hasFile ? 'document-status uploaded' : 'document-status not-uploaded';
    }
  });
}

function viewUploadedFile(inputId) {
  const fileInput = document.getElementById(inputId);
  if (fileInput && fileInput.files && fileInput.files.length > 0) {
    if (inputId === 'passport_pictures_file') {
      viewPassportPictures(fileInput.files);
    } else {
      viewSingleFile(fileInput.files[0], fileInput.getAttribute('data-title') || 'Document');
    }
  } else {
    alert('No file uploaded for this document.');
  }
}

// Add event listeners for modal close functionality
document.addEventListener('DOMContentLoaded', function () {
  // Close modal when clicking the X button
  const closeBtn = document.querySelector('.review-modal-close');
  if (closeBtn) {
    closeBtn.addEventListener('click', closeReviewModal);
  }

  // Close modal when clicking outside the modal content
  const modal = document.getElementById('reviewModal');
  if (modal) {
    modal.addEventListener('click', function (e) {
      if (e.target === modal) {
        closeReviewModal();
      }
    });
  }

  // Close modal with Escape key
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      const modal = document.getElementById('reviewModal');
      if (modal && modal.style.display === 'block') {
        closeReviewModal();
      }
    }
  });
});