@if(!($adminStoreBrowse ?? \App\Support\AuthPanel::isAdminStoreBrowse()))
{{ $slot }}
@endif
