$(document).ready(function() {
    // Initialize with default values
    updateSummary();
    updateExpiryDate();

    // Generate default transaction ID on load
    if (!$('#transaction_id').val() || $('#transaction_id').val() === '') {
        generateTransactionId();
    }

    // NEW: Auto-fill mobile number when station is selected
    $('#station_id').on('change', function() {
        autoFillStationMobile();
        updateSummary();
    });

    // Update summary on form changes
    $('#station_id, #mobile_number, #amount, #topup_date, #network_provider, #transaction_id, #payment_method').on('change input', function() {
        updateSummary();
    });

    // Calculate expected expiry when topup date changes
    $('#topup_date').on('change', function() {
        updateExpiryDate();
        updateSummary();
    });

    // Network provider info
    $('#network_provider').on('change', function() {
        showNetworkInfo(this.value);
        updateSummary();
    });

    // Set default topup date to today if not set
    if (!$('#topup_date').val()) {
        setDate('today');
    }

    // Format mobile number as user types
    $('#mobile_number').on('input', formatMobileNumber);

    // Auto-select network provider based on mobile number prefix
    $('#mobile_number').on('blur', autoDetectNetwork);

    // Make functions globally available
    window.setAmount = setAmount;
    window.generateTransactionId = generateTransactionId;
    window.addNote = addNote;
    window.setDate = setDate;
    window.setExpiryDays = setExpiryDays;
    window.showStationDetails = showStationDetails;
    window.copyStationMobile = copyStationMobile;
    window.showExpiryOptions = showExpiryOptions;
    window.clearForm = clearForm;
    window.saveAsDraft = saveAsDraft;
});

// NEW FUNCTION: Auto-fill mobile number from selected station
function autoFillStationMobile() {
    const stationSelect = $('#station_id');
    const selectedOption = stationSelect.find('option:selected');
    const stationMobile = selectedOption.data('mobile');

    if (stationMobile && stationMobile.trim() !== '') {
        // Only fill if mobile field is empty or contains default/placeholder
        const currentMobile = $('#mobile_number').val().trim();
        if (!currentMobile || currentMobile === 'e.g., 0712345678') {
            $('#mobile_number').val(stationMobile).trigger('input');
            autoDetectNetwork(); // Auto-detect network after filling
        }
    }
}

// MODIFIED copyStationMobile function
function copyStationMobile() {
    const stationSelect = $('#station_id');
    const selectedOption = stationSelect.find('option:selected');
    const stationMobile = selectedOption.data('mobile');

    if (stationMobile && stationMobile.trim() !== '') {
        $('#mobile_number').val(stationMobile).trigger('input');
        autoDetectNetwork();
        updateSummary();
    } else {
        alert('No mobile number found for this station');
    }
}

function updateSummary() {
    const stationSelect = $('#station_id');
    const selectedStation = stationSelect.find('option:selected');

    $('#summaryStation').text(selectedStation.text() || '-');
    $('#summaryMobile').text($('#mobile_number').val() || '-');
    $('#summaryNetwork').text($('#network_provider').val() || '-');
    $('#summaryAmount').text('KES ' + (parseFloat($('#amount').val()) || 0).toLocaleString('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }));
    $('#summaryDate').text($('#topup_date').val() || '-');
    $('#summaryExpiry').text($('#expected_expiry').val() || '-');
    $('#summaryTxn').text($('#transaction_id').val() || '-');
    $('#summaryMethod').text($('#payment_method').val() || '-');
}

function updateExpiryDate() {
    const topupDate = $('#topup_date').val();
    if (topupDate) {
        const days = $('input[name="expiryOption"]:checked').val() || 30;
        const expiryDate = new Date(topupDate);
        expiryDate.setDate(expiryDate.getDate() + parseInt(days));

        const formattedDate = expiryDate.toISOString().split('T')[0];
        $('#expected_expiry').val(formattedDate);
        $('#expiryText').text(`Auto-calculated: ${days} days from topup date`);
    }
}

function setExpiryDays(days) {
    // First update the radio button
    $(`input[name="expiryOption"][value="${days}"]`).prop('checked', true);
    updateExpiryDate();
    updateSummary();
}

function showNetworkInfo(network) {
    const info = {
        'Safaricom': 'Kenya\'s largest mobile network with 4G coverage',
        'Airtel': 'Affordable calling rates and data bundles',
        'Telkom': 'Formerly Orange, good for data packages',
        'Faiba': 'JTL network with competitive data rates'
    };

    if (info[network]) {
        $('#networkInfo').show();
        $('#networkDescription').text(info[network]);
    } else {
        $('#networkInfo').hide();
    }
}

function showStationDetails() {
    const stationId = $('#station_id').val();
    if (!stationId) {
        alert('Please select a station first');
        return;
    }

    const stationSelect = $('#station_id');
    const selectedOption = stationSelect.find('option:selected');

    const content = `
        <h6>${selectedOption.text()}</h6>
        <p><strong>Contact Person:</strong> ${selectedOption.data('contact') || 'N/A'}</p>
        <p><strong>Phone:</strong> ${selectedOption.data('mobile') || 'N/A'}</p>
        <p><strong>Email:</strong> ${selectedOption.data('email') || 'N/A'}</p>
        <p><strong>Location:</strong> ${selectedOption.data('location') || 'N/A'}</p>
    `;

    $('#stationModalBody').html(content);
    new bootstrap.Modal($('#stationModal')).show();
}

function setAmount(amount) {
    $('#amount').val(amount);
    updateSummary();
}

function setDate(type) {
    const today = new Date();
    let date;

    if (type === 'today') {
        date = today;
    } else if (type === 'yesterday') {
        date = new Date(today);
        date.setDate(date.getDate() - 1);
    }

    const formattedDate = date.toISOString().split('T')[0];
    $('#topup_date').val(formattedDate);
    updateExpiryDate();
    updateSummary();
}

function generateTransactionId() {
    const timestamp = new Date().getTime();
    const random = Math.floor(Math.random() * 1000);
    const txnId = 'TXN' + timestamp + random;
    $('#transaction_id').val(txnId);
    updateSummary();
}

function showExpiryOptions() {
    // You can enhance this with a modal for more options
    alert('Use the radio buttons to select expiry duration (30, 60, or 90 days)');
}

function addNote(note) {
    const currentNotes = $('#notes').val();
    if (currentNotes) {
        $('#notes').val(currentNotes + '\n' + note);
    } else {
        $('#notes').val(note);
    }
}

function formatMobileNumber() {
    let value = this.value.replace(/\D/g, '');

    // Ensure it starts with 0 or 254
    if (value.length > 0 && !value.startsWith('0') && !value.startsWith('254')) {
        value = '0' + value;
    }

    // Format as Kenyan phone number
    if (value.startsWith('254') && value.length === 12) {
        value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{3})/, '$1 $2 $3 $4');
    } else if (value.startsWith('0') && value.length === 10) {
        value = value.replace(/(\d{4})(\d{3})(\d{3})/, '$1 $2 $3');
    }

    this.value = value;
    updateSummary();
}

function autoDetectNetwork() {
    const number = $('#mobile_number').val().replace(/\s/g, '').replace(/\D/g, '');

    if (number.length >= 3) {
        let prefix = number;
        if (number.startsWith('254')) {
            prefix = number.substring(3, 6);
        } else if (number.startsWith('0')) {
            prefix = number.substring(1, 4);
        }

        const providerMap = {
            '070': 'Safaricom', '071': 'Safaricom', '072': 'Safaricom', '074': 'Safaricom', '079': 'Safaricom',
            '075': 'Airtel', '076': 'Airtel',
            '077': 'Telkom', '078': 'Telkom',
            '010': 'Faiba', '011': 'Faiba'
        };

        if (providerMap[prefix]) {
            $('#network_provider').val(providerMap[prefix]).trigger('change');
        }
    }
}

function clearForm() {
    if (confirm('Are you sure you want to clear all form data?')) {
        document.getElementById('airtimeForm').reset();
        setDate('today');
        generateTransactionId();
        updateSummary();
        updateExpiryDate();
    }
}

function saveAsDraft() {
    // Implement draft saving functionality
    alert('Draft saving feature will be implemented soon!');
}

