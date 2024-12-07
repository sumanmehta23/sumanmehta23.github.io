@extends('layouts.admin.admin')
@section('content')

<!-- Start::app-content -->
<div class="main-content app-content">
  <div class="container-fluid">
    <div class="page-header">
      <h1 class="page-title">IB Requests</h1>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">IB</li>
      </ol>
    </div>
    <div class="row">
      <div class="col-xl-12">
        <div class="card custom-card">
          <div class="card-body">
            <div class="table-responsive">
              <table id="tableIbUsers" class="table ajaxDataTable table-bordered text-nowrap w-100">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <!-- <th>Country</th>
                    <th>Number</th> -->
                    <th>Tot. Comm.</th>
                    <th>Tot. Withdrawal</th>
                    <th>Status / Action</th>
                    <th>Reg. Date</th>
                    <!-- <th>Action</th>   -->
                  </tr>
                </thead>
                <tbody>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- End::app-content -->

<!-- Modal -->
<div class="modal fade" id="ibModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="ibModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="#" id="ibRequestForm"  method="POST">
        @csrf
        <input type="hidden" name="client_id" id="client_id" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="ibModalLabel">IB Request Management</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="mb-0 modal-body custom-card card">
          <div class="d-flex align-items-center card-header w-100">
            <div class="me-2">
              <span class="avatar avatar-rounded">
                <img src="/admin_assets/assets/images/users/user.png" alt="img">
              </span>
            </div>
            <div class="">
              <div class="fs-15 fw-medium text-capitalize" id="clientName"></div>
              <p class="mb-0 text-muted fs-11" id="clientEmail"></p>
            </div>

          </div>
          <div class="card-body">
            <div class="mb-3 row">
              <div class="m-auto col-lg-4">
                <label class="form-label">IB Request Status</label>
              </div>
              <div class="col-lg-8">
                <select class="form-select" required name="ib_status" aria-label="Default select example">
                  <option value="" selected>--Status--</option>
                  <option value="1">Approve</option>
                  <option value="0">Pending</option>
                  <option value="2">Rejected</option>
                </select>
              </div>
            </div>
            <div class="row">
              <div class="m-auto col-lg-4">
                <label class="form-label">Account Group</label>
              </div>
              <div class="col-lg-8">
                <select class="form-select" required name="ib_group" aria-label="Default select example">
                  <option value="" selected>--Plans--</option>
                  <?php foreach ($acc_groups as $gp) { ?>
                    <option value="<?= $gp->ib_plan_id ?>"><?= $gp->ib_cat_name ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name="ibRequest" value="update" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
@section("scripts")
<script>
  $(document).ready(function() {
    window.myModal = new bootstrap.Modal(document.getElementById('ibModal'));
  });

  function dTSelection() {
    // alert("Init");
    $('.ajaxDataTable tbody tr').off();
    $('.ajaxDataTable tbody tr').on('click', '.ibToggle', function() {
      var data = dTtable.row($(this).closest("tr")).data();
      // console.log(data);
      $("#ibRequestForm input,#ibRequestForm select").not("input[name='_token']").val("").trigger("change");
      $("#clientName,#clientEmail").html("");
      $("#clientName").html(data.name)
      $("#clientEmail").html(data.email)
      $("#client_id").val(data.enc)
      $("[name='ib_status']").val(data.status).trigger("change");
      $("[name='ib_group']").val(data.acc_type).trigger("change");
      myModal.show();
      // swal.fire({
      //   icon: "info",
      //   title: "IB Status ==> " + data.ib_status
      // });

    });
  }

  $(document).ready(function() {
    window.dTtable = $('#tableIbUsers').on("draw.dt", dTSelection).DataTable({
      // order: [[0, "desc"]],
      destroy: true,
      "ajax": {
        "url": "/admin/ajax",
        "type": "GET",
        data: {
          action: 'getPendingIbUsers',
        },
      },
      columns: [{
          data: 'id',
          name: 'id'
        },
        {
          data: 'name',
          name: 'name',
          render: function(data,row,row_data){
            var small = "";
            if(row_data.grp != null) {small = '<small>'+row_data.grp+'</small>';}
            var return_data = "<a href='/admin/client_details/" + row_data.enc + "'><div class='d-flex align-items-center'><div class='me-2'><svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg></div><div><div class='lh-1'><span>" + row_data.name + "</span></div><div class='lh-1'><span class='fs-11 text-muted'>" + row_data.email + "</span></div>"+small+"</div></div></a>";
            return return_data;
          }
        },
        // {
        //   data: 'country',
        //   name: 'country'
        // },
        // {
        //   data: 'number',
        //   name: 'number'
        // },
        {
          data: 'total_deposit',
          name: 'total_deposit'
        },
        {
          data: 'total_withdrawal',
          name: 'total_withdrawal'
        },
        {
          data: 'status',
          name: 'status',
          render: function(data) {
            if (data == 1) {
              return "<button class='ibToggle badge btn-sm btn btn-outline-success'>Active IB</button>";
            } else if (data == 2) {
              return "<button class='ibToggle badge btn-sm btn btn-outline-danger'>Rejected</button>";
            } else if (data == 0) {
              return "<button class='ibToggle badge btn-sm btn btn-outline-info'>IB Requested</button>";
            } else {
              return "<button class='ibToggle badge btn-sm btn btn-outline-primary'>Not Requested</button>";
            }
          }
        },
        {
          data: 'date',
          name: 'date',
          render: function(data) {
            var dd = data.split(" ");
            var rend_date = dd[0]+"<br><small>"+dd[1]+"</small>";
            return rend_date;
          }
        }
        // { data: 'action', name: 'action', orderable: false, searchable: false },
      ]
    });
  });
</script>
@endsection
