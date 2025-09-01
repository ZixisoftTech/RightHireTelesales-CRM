<?php
/**
 * Update the lead status update view
 * 
 * This file contains the HTML for the lead status update modal.
 */
?>
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">Update Lead Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?php echo APP_URL; ?>/leads/update-status?id=<?php echo $lead['id']; ?>" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="not_attend">Not Attend</option>
                            <option value="wrong_number">Wrong Number</option>
                            <option value="other">Other</option>
                            <option value="dead">Lost</option>
                            <option value="interested">Interested</option>
                            <option value="win">Won</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="regionField" style="display: none;">
                        <label for="region" class="form-label">Region</label>
                        <input type="text" class="form-control" id="region" name="region">
                        <div class="form-text">Required for Lost status</div>
                    </div>
                    
                    <div class="mb-3" id="followUpDateField" style="display: none;">
                        <label for="follow_up_date" class="form-label">Follow-up Date & Time</label>
                        <input type="datetime-local" class="form-control" id="follow_up_date" name="follow_up_date">
                        <div class="form-text">Required for Follow-up and Interested status</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3"></textarea>
                        <div class="form-text" id="remarksHelp">Required for Not Attend and Wrong Number status</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide fields based on selected status
    document.getElementById('status').addEventListener('change', function() {
        var status = this.value;
        var followUpDateField = document.getElementById('followUpDateField');
        var regionField = document.getElementById('regionField');
        var remarksHelp = document.getElementById('remarksHelp');
        
        // Reset display
        followUpDateField.style.display = 'none';
        regionField.style.display = 'none';
        remarksHelp.textContent = '';
        
        // Show fields based on status
        if (status === 'follow_up' || status === 'interested') {
            followUpDateField.style.display = 'block';
            document.getElementById('follow_up_date').required = true;
        } else {
            document.getElementById('follow_up_date').required = false;
        }
        
        if (status === 'dead') {
            regionField.style.display = 'block';
            document.getElementById('region').required = true;
        } else {
            document.getElementById('region').required = false;
        }
        
        if (status === 'not_attend' || status === 'wrong_number') {
            remarksHelp.textContent = 'Required for ' + (status === 'not_attend' ? 'Not Attend' : 'Wrong Number') + ' status';
            document.getElementById('remarks').required = true;
        } else {
            document.getElementById('remarks').required = false;
        }
    });
});
</script>

