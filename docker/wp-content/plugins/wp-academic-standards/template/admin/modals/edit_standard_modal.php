<!-- Modal -->
<?php global $post; ?>
<div class="modal fade" id="editStandardModal" tabindex="-1" role="dialog" aria-labelledby="standardModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="standardModalLabel">Edit Standard</h4>
      </div>
      <div id="edit-standard" class="modal-body">
        <div id="edit-core-standard" class="hidden-block">
          <div class="form-group">
            <input type="hidden" id="standard_id">
            <label for="standard_name">Standard Name:</label>
            <input type="text" class="form-control" id="standard_name">
          </div>
          <div class="form-group">
            <label for="standard_url">Standard URL:</label>
            <input type="text" class="form-control" id="standard_url">
          </div>
        </div>
        <div id="edit-sub-standard" class="hidden-block">
          <div class="form-group">
            <input type="hidden" id="substandard_id">
            <input type="hidden" class="form-control" id="substandard_parent_id">
            <label for="">Standard Title:</label>
            <input type="text" class="form-control" id="substandard_title">
          </div>
          <div class="form-group">
            <label for="">Standard URL:</label>
            <input type="text" class="form-control" id="substandard_url">
          </div>
        </div>
        <div id="edit-standard-notation" class="hidden-block">
          <div class="form-group">
            <input type="hidden" class="form-control" id="notation_id">
            <input type="hidden" class="form-control" id="notation_parent_id">
            <label for="">Prefix:</label>
            <input type="text" class="form-control" id="standard_notation">
          </div>
          <div class="form-group">
            <label for="">Standard Notation:</label>
            <input type="text" class="form-control" id="description">
          </div>
          <div class="form-group">
            <label for="">Comment:</label>
            <input type="text" class="form-control" id="comment">
          </div>
          <div class="form-group">
            <label for="">Notation URL:</label>
            <input type="text" class="form-control" id="notation_url">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="btnUpdateStandards" class="btn btn-default btn-sm" data-postid="<?php echo $post->ID; ?>" data-dismiss="modal">Update</button>
      </div>
    </div>
  </div>
</div>