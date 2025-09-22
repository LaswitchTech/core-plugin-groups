<article id="layout"></article>
<script>
    (function () {
        $(document).ready(function(){
            builder.Layout('group',"#layout",{endpoint: '/groups/fetch?id=<?= $this->Request->getParams('GET', 'id') ?>'});
        });
    })();
</script>
