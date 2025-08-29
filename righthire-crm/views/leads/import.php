<?php include 'views/templates/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Import Leads</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo APP_URL; ?>/leads" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Leads
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Instructions</h5>
    </div>
    <div class="card-body">
        <p>Follow these steps to import leads:</p>
        <ol>
            <li>Prepare a CSV file with lead data.</li>
            <li>The first row should contain column headers.</li>
            <li>Required fields: Name, Phone, State ID, City ID.</li>
            <li>Optional fields: Email, Address, Remarks.</li>
            <li>Upload the CSV file using the form below.</li>
            <li>Map the columns to the appropriate fields.</li>
            <li>Click "Import Leads" to start the import process.</li>
        </ol>
        <p>Example CSV format:</p>
        <pre>Name,Email,Phone,Address,State ID,City ID
John Doe,john@example.com,1234567890,123 Main St,1,1
Jane Smith,jane@example.com,0987654321,456 Oak Ave,2,3</pre>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Import Form</h5>
    </div>
    <div class="card-body">
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo APP_URL; ?>/leads/import" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="csv-file" class="form-label required">CSV File</label>
                <input type="file" class="form-control" id="csv-file" name="csv_file" accept=".csv" required>
                <div class="form-text">Maximum file size: <?php echo MAX_UPLOAD_SIZE / 1024 / 1024; ?>MB</div>
            </div>
            
            <div id="csv-preview" class="mb-3"></div>
            
            <div id="csv-mapping" class="d-none">
                <h5 class="mb-3">Column Mapping</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="mapping-name" class="form-label required">Name</label>
                        <select class="form-select mapping-select" id="mapping-name" name="mapping[name]" required></select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="mapping-email" class="form-label">Email</label>
                        <select class="form-select mapping-select" id="mapping-email" name="mapping[email]"></select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="mapping-phone" class="form-label required">Phone</label>
                        <select class="form-select mapping-select" id="mapping-phone" name="mapping[phone]" required></select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="mapping-address" class="form-label">Address</label>
                        <select class="form-select mapping-select" id="mapping-address" name="mapping[address]"></select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="mapping-state-id" class="form-label required">State ID</label>
                        <select class="form-select mapping-select" id="mapping-state-id" name="mapping[state_id]" required></select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="mapping-city-id" class="form-label required">City ID</label>
                        <select class="form-select mapping-select" id="mapping-city-id" name="mapping[city_id]" required></select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="mapping-remarks" class="form-label">Remarks</label>
                        <select class="form-select mapping-select" id="mapping-remarks" name="mapping[remarks]"></select>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="<?php echo APP_URL; ?>/leads" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Import Leads</button>
            </div>
        </form>
    </div>
</div>

<?php include 'views/templates/footer.php'; ?>

