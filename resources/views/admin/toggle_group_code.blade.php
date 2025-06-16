 <div class="card custom-card">
     <div class="card-header">
         <div class="card-title">Toggle Group Code</div>
     </div>
     <div class="card-body p-4">
         <form method="POST" action="{{ route('admin.toggle_group_code.update') }}">
             @csrf
             <div class="card border-0 bg-light mb-4">
                 <div class="card-body p-3">
                     <div class="row align-items-center">
                         <div class="col-md-8">
                             <div class="d-flex align-items-center">
                                 <div class="me-3">
                                     <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 50px; height: 50px;">
                                         <i class="fab fa-bitcoin fs-4"></i>
                                     </div>
                                 </div>
                                 <div>
                                     <h5 class="mb-1 fw-bold">Group Codes</h5>
                                     <p class="text-muted mb-0 small">Toggle clients group code from A-Book to B-Book and vice versa</p>
                                 </div>
                             </div>
                         </div>
                         <div class="col-md-4 text-md-end mt-3 mt-md-0">
                             <div class="d-flex align-items-center justify-content-md-end gap-3">
                                 <span class="badge bg-secondary" id="group-status">Inactive</span>
                                 <div class="form-check form-switch">
                                     <input class="form-check-input fs-5" type="checkbox" id="enableGroup"
                                         name="enable_group" value="1"
                                         {{ isset($settings['enable_group']) && $settings['enable_group'] == '1' ? 'checked' : '' }}
                                         onchange="updateStatus('group', this.checked)">
                                     <label class="form-check-label visually-hidden" for="enableGroup">Enable
                                         Group</label>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>

             <hr class="my-4">
             <div class="d-flex justify-content-end">
                 <button type="submit" class="btn btn-primary btn-sm">
                     <i class="fas fa-save me-2"></i>
                     Save Settings
                 </button>
             </div>
         </form>
     </div>
 </div>
 <script>
     function updateStatus(gateway, isEnabled) {
         const statusBadge = document.getElementById(gateway + '-status');

         if (isEnabled) {
             statusBadge.textContent = 'Active';
             statusBadge.className = 'badge bg-success';
         } else {
             statusBadge.textContent = 'Inactive';
             statusBadge.className = 'badge bg-secondary';
         }
     }

     // Initialize status on page load
     document.addEventListener('DOMContentLoaded', function() {
         const groupCheckbox = document.getElementById('enableGroup');

         updateStatus('group', groupCheckbox.checked);
     });
 </script>
