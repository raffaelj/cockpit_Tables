
<style>
@if($table['color'])
.app-header { border-top: 8px {{ $table['color'] }} solid; }
@endif
</style>

<script>

function TableHasFieldAccess(field) {

    var acl = field.acl || [];

    if (field.name == '_modified' ||
        App.$data.user.group == 'admin' ||
        !acl ||
        (Array.isArray(acl) && !acl.length) ||
        acl.indexOf(App.$data.user.group) > -1 ||
        acl.indexOf(App.$data.user._id) > -1
    ) { return true; }

    return false;
}

</script>

@render('tables:views/partials/breadcrumbs.php', compact('table'))

@render('tables:views/partials/entries.php', compact('table'))
