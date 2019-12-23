<!--Dynamic module modal-->
<div class="modal fade" id="lp-dynamic-module-modal" tabindex="-1" role="dialog" aria-labelledby="lpDynamicModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h3 class="modal-title" id="lpDynamicModalLabel">Add Module</h3>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="module-type" class="control-label">Module Type</label>
                    <select name="module-type" class="form-control" id="module-type">
                        <option value="editor">Text/Editor</option>
                        <option value="list">Text List</option>
                        <option value="materials">Materials</option>
                        <option value="vocabulary">Vocabulary List</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="lp-create-module-btn">Create</button>
            </div>
        </div>
    </div>
</div>