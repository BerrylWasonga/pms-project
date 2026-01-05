<?php
require_once('./../../config.php');
if(isset($_GET['id']) && $_GET['id'] > 0){
    $qry = $conn->query("SELECT * FROM `visit_list` WHERE id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        foreach($qry->fetch_assoc() as $k => $v){
            $$k = $v;
        }
    }
}
?>
<div class="container-fluid">
    <form action="" id="visit-form">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <div class="form-group">
            <label for="inmate_id" class="control-label">Inmate</label>
            <select name="inmate_id" id="inmate_id" class="form-control form-control-sm rounded-0" required>
                <option value="" <?= !isset($inmate_id) ? 'selected' : '' ?>>-- Select Inmate --</option>
                <?php 
                $inmates = $conn->query("SELECT *, CONCAT(lastname, ', ', firstname, COALESCE(CONCAT(' ', middlename), '')) AS `name`
                                         FROM `inmate_list` 
                                         WHERE `status` = 1 
                                         AND (date_to IS NULL OR date_to >= CURDATE())
                                         AND visiting_privilege = 1");
                while($row = $inmates->fetch_assoc()):
                ?>
                <option value="<?= $row['id'] ?>" <?= isset($inmate_id) && $inmate_id == $row['id'] ? 'selected' : '' ?>>
                    <?= "Inmate-{$row['code']} {$row['name']}" ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="fullname" class="control-label">Visitor's Full Name</label>
            <input type="text" name="fullname" id="fullname" class="form-control form-control-sm rounded-0" 
                   value="<?php echo isset($fullname) ? $fullname : ''; ?>" required/>
        </div>
        <div class="form-group">
            <label for="contact" class="control-label">Contact #</label>
            <input type="text" name="contact" id="contact" class="form-control form-control-sm rounded-0" 
                   value="<?php echo isset($contact) ? $contact : ''; ?>" required/>
        </div>
        <div class="form-group">
            <label for="relation" class="control-label">Relation</label>
            <input type="text" name="relation" id="relation" class="form-control form-control-sm rounded-0" 
                   value="<?php echo isset($relation) ? $relation : ''; ?>" required/>
        </div>
    </form>
</div>

<script>
    $(document).ready(function(){
        $('#uni_modal').on('shown.bs.modal', function(){
            $('#inmate_id').select2({
                placeholder: 'Please select an inmate',
                width: '100%',
                dropdownParent: $('#uni_modal'),
                containerCssClass: 'form-control form-control-sm rounded-0'
            })
        })

        $('#visit-form').submit(function(e){
            e.preventDefault();
            var _this = $(this);
            $('.err-msg').remove();
            start_loader();
            $.ajax({
                url: _base_url_ + "classes/Master.php?f=save_visit",
                data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
                error: err => {
                    console.log(err);
                    alert_toast("An error occurred", 'error');
                    end_loader();
                },
                success: function(resp) {
                    if (typeof resp === 'object' && resp.status === 'success') {
                        alert_toast(resp.msg, 'success');
                        uni_modal("<i class='fa fa-th-list'></i> Visitor Details ", "visits/view_visit.php?id=" + resp.cid);
                        $('#uni_modal').on('hide.bs.modal', function() {
                            location.reload();
                        })
                    } else if (resp.status === 'failed' && !!resp.msg) {
                        var el = $('<div>');
                        el.addClass("alert alert-danger err-msg").text(resp.msg);
                        _this.prepend(el);
                        el.show('slow');
                        $("html, body").scrollTop(0);
                        end_loader();
                    } else {
                        alert_toast("An error occurred", 'error');
                        end_loader();
                        console.log(resp);
                    }
                }
            })
        })
    })
</script>
