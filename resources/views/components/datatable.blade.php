@props(['dataTable'])
@push('css')
   @include('layouts.includes.datatable-css')
@endpush
<div class="card">
   <div class="card-body table-border-style">
      <div class="table-responsive ecom-data-table">
           {!! $dataTable->table(['width' => '100%']) !!}
       </div>
   </div>
</div>
@push('scripts')
   @include('layouts.includes.datatable-js')
   {{--
      Turbo Drive (CLAUDE-6).

      yajra emits, from vendor/yajra/laravel-datatables-html/.../script.blade.php:

         $(function(){ window.LaravelDataTables["x"] = $("#x").DataTable({...}) });

      That file is in vendor/ and cannot be edited, and Turbo re-executes body
      scripts after every page swap — so on a cached back-navigation the same
      $().DataTable() call lands on markup DataTables has already wrapped:
      "Cannot reinitialise DataTable", and the table is left in a half-built
      state with no working search or paging.

      Fixed centrally instead, in resources/js/turbo-cleanup.js: every instance
      is destroyed on turbo:before-cache and turbo:before-render, so the cached
      snapshot holds a plain <table> and the re-executed script initialises it
      cleanly. Nothing needs to change in this component for that to work, which
      is why the vendor call below is untouched.

      Two lines did change, both below: the $(document).ready wrapper, and one
      guard. See the comments there.
   --}}
   {{ $dataTable->scripts() }}

   @if (module_is_active('BulkDelete'))
      @include('bulk-delete::pages.script')
   @endif

   <script>
   {{--
      $(document).ready → onTurboLoad: ready() fires once per document, so from
      the second Turbo page onward the header fix-up never ran and the sorting
      arrow reappeared on checkbox columns.
   --}}
   window.onTurboLoad(function () {
      removeSortingOrderFromHeader();
   });
   function removeSortingOrderFromHeader() {
      // Remove 'sorting_desc' class from all <th> elements containing a checkbox
      $('table').each(function() {
         $(this).find('th').each(function() {
            if (($(this).hasClass('sorting_desc') || $(this).hasClass('sorting_asc')) && $(this).find('input[type="checkbox"]').length) {
                  $(this).removeClass('sorting sorting_asc sorting_desc');
            }
         });
      });

      // Ensure class is removed every time sorting is triggered on any DataTable
      // .off() before .on() (CLAUDE-6): this function is also called from the
      // initComplete and drawCallback of ~70 tables, so on a server-side table
      // every draw used to add another order.dt handler to the same <table>.
      $('table').off('order.dt.removeSorting').on('order.dt.removeSorting', function() {
         $(this).find('th').each(function() {
            if (($(this).hasClass('sorting_desc') || $(this).hasClass('sorting_asc')) && $(this).find('input[type="checkbox"]').length) {
                  $(this).removeClass('sorting sorting_asc sorting_desc');
            }
         });
      });
   }
   </script>
@endpush
