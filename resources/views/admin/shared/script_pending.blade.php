<script>
    $(document).ready(function () {
      $('#tableWalletDeposit').DataTable({
        // order: [[0, "desc"]],
        // "ajax": {
        //   "url": "/admin/ajax",
        //   "type": "GET",
        //   data: {
        //     action: 'getPendingWalletDeposit',
        //   },
        // },
        dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
        buttons: [
                {
                    extend: 'excel',
                    text: 'Export to Excel',
                    exportOptions: {
                        columns: [6,7,1,2,4,8,9] // Updated column indices to match your use case
                    }
                }
            ],

        order: [[3, "desc"]],
        processing: true,
        serverSide: true,
        searching: true,
        ajax: {
            url: '/admin/getPendingWalletDeposit2',
            type: 'GET',
            data: {}, // Ensure this is populated dynamically if needed.
            dataSrc: function(json) {
                return json.data;
            }
        },
        columns: [
          {
            data: 'email', name: 'email', render: function (data, row, row_data) {
              var return_data = "<a href='/admin/client_details/" + row_data.enc_id + "'><div class='d-flex align-items-center'><div class='me-2'><svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg></div><div><div class='lh-1'><span>" + row_data.fullname + "</span></div><div class='lh-1'><span class='fs-11 text-muted'>" + row_data.email + "</span></div></div></div></a>";
              return return_data;
            }
          },
          { data: 'amount', name: 'amount' },
          { data: 'payment_mode', name: 'payment_mode' },
          {
            data: 'deposit_date', name: 'deposit_date', render: function (data, type, row) {
              var dateTime = row.deposit_date.split(' ');
              var date = dateTime[0];
              var time = dateTime[1];
              var return_data = "<div class='d-grid'><div class='date'>" + date + "</div><div class='time text-muted'>" + time + "</div></div>";
              return return_data;
            }
          },
          { data: 'status', name: 'status' },
          { data: 'action', name: 'action', orderable: false, searchable: false },
          { data: 'fullname', name: 'fullname', visible: false },
          { data: 'fullemail', name: 'fullemail', visible: false},
          { data: 'created_date', name: 'created_date', visible: false},
          { data: 'created_time', name: 'created_time', visible: false},
        ]
      });
      $('#tableWalletWithdrawal').DataTable({
        // order: [[0, "desc"]],
        // "ajax": {
        //   "url": "/admin/ajax",
        //   "type": "GET",
        //   data: {
        //     action: 'getPendingWalletWithdrawal',
        //   },
        // },
        dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
        buttons: [
                {
                    extend: 'excel',
                    text: 'Export to Excel',
                    exportOptions: {
                        columns: [7,8,1,2,3,9,10,5] // Updated column indices to match your use case
                    }
                }
            ],

        order: [[3, "desc"]],
        processing: true,
        serverSide: true,
        searching: true,
        ajax: {
            url: '/admin/getPendingWalletWithdrawal2',
            type: 'GET',
            data: {}, // Ensure this is populated dynamically if needed.
            dataSrc: function(json) {
                return json.data;
            }
        },
        columns: [
          {
            data: 'email', name: 'email',
            // render: function (data, row, row_data) {
            //   var return_data = "<a href='/admin/client_details/" + row_data.enc_id + "'><div class='d-flex align-items-center'><div class='me-2'><svg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='#000000' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round' size='28' color='#000000' class='tabler-icon tabler-icon-user-square-rounded'><path d='M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z'></path><path d='M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z'></path><path d='M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05'></path></svg></div><div><div class='lh-1'><span>" + row_data.fullname + "</span></div><div class='lh-1'><span class='fs-11 text-muted'>" + row_data.email + "</span></div></div></div></a>";
            //   return return_data;
            // }
          },
          { data: 'amount', name: 'amount' },
          { data: 'fee', name: 'fee' },
          { data: 'payment_mode', name: 'payment_mode' },
          {
            data: 'withdraw_date', name: 'withdraw_date',
            // render: function (data, type, row) {
            //   var dateTime = row.withdraw_date.split(' ');
            //   var date = dateTime[0];
            //   var time = dateTime[1];
            //   var return_data = "<div class='d-grid'><div class='date'>" + date + "</div><div class='time text-muted'>" + time + "</div></div>";
            //   return return_data;
            // }
          },
          { data: 'status', name: 'status' },
          { data: 'action', name: 'action', orderable: false, searchable: false },
          { data: 'fullname', name: 'fullname', visible: false },
          { data: 'fullemail', name: 'fullemail', visible: false},
          { data: 'created_date', name: 'created_date', visible: false},
          { data: 'created_time', name: 'created_time', visible: false},
        ]
      });
      $('#tableTradingDeposit').DataTable({
        // order: [[0, "desc"]],
        // "ajax": {
        //   "url": "/admin/ajax",
        //   "type": "GET",
        //   data: {
        //     action: 'getPendingTradingDeposit',
        //   },
        // },
        // order: [['0','desc']],
        dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
        buttons: [
                {
                    extend: 'excel',
                    text: 'Export to Excel',
                    exportOptions: {
                        columns: [0,1,2,3,4,8,9,6] // Updated column indices to match your use case
                    }
                }
            ],

        order: [[3, "desc"]],
        processing: true,
        serverSide: true,
        searching: true,
        ajax: {
            url: '/admin/getPendingTradingDeposit2',
            type: 'GET',
            data: {}, // Ensure this is populated dynamically if needed.
            dataSrc: function(json) {
                return json.data;
            }
        },
        columns: [
          { data: 'id', name: '#',render: function(data){
            var dd = data;
            return "TWID"+dd.toString().padStart(4,0);
          } },
          { data: 'account_no', name: 'account_no' },
          { data: 'amount', name: 'amount' },
          { data: 'deposit_type', name: 'deposit_type' },
          { data: 'deposit_from', name: 'deposit_from' },
          {
            data: 'deposit_date', name: 'deposit_date', render: function (data, type, row) {
              var dateTime = row.deposit_date.split(' ');
              var date = dateTime[0];
              var time = dateTime[1];
              var return_data = "<div class='d-grid'><div class='date'>" + date + "</div><div class='time text-muted'>" + time + "</div></div>";
              return return_data;
            }
          },
          { data: 'status', name: 'status' },
          { data: 'action', name: 'action', orderable: false, searchable: false },
          { data: 'created_date', name: 'created_date', visible: false},
          { data: 'created_time', name: 'created_time', visible: false},
        ]
      });
      $('#tableTradingWithdrawal').DataTable({
        dom: '<"row" <"col"B><"col text-center"l><"col"f>><"row"<"col"t>><"row"<"col"i><"col"p>>',
        buttons: [
                {
                    extend: 'excel',
                    text: 'Export to Excel',
                    exportOptions: {
                        columns: [0,1,2,3,5,7,8] // Updated column indices to match your use case
                    }
                }
            ],

        order: [[3, "desc"]],
        processing: true,
        serverSide: true,
        searching: true,
        ajax: {
            url: '/admin/getPendingTradingWithdrawal2',
            type: 'GET',
            data: {}, // Ensure this is populated dynamically if needed.
            dataSrc: function(json) {
                return json.data;
            }
        },
        columns: [
          { data: 'account_no', name: 'account_no' },
          { data: 'amount', name: 'amount' },
          { data: 'withdraw_type', name: 'withdraw_type' },
          { data: 'withdraw_to', name: 'withdraw_to' },
          {
            data: 'withdraw_date', name: 'withdraw_date'
            // render: function (data, type, row) {
            //   var dateTime = row.withdraw_date.split(' ');
            //   var date = dateTime[0];
            //   var time = dateTime[1];
            //   var return_data = "<div class='d-grid'><div class='date'>" + date + "</div><div class='time text-muted'>" + time + "</div></div>";
            //   return return_data;
            // }
          },
          { data: 'status', name: 'status' },
          { data: 'action', name: 'action', orderable: false, searchable: false },
          { data: 'created_date', name: 'created_date', visible: false},
          { data: 'created_time', name: 'created_time', visible: false},
        ]
      });

    });
  </script>
