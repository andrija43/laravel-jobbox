(()=>{var o,a,n,t;o=jQuery,a=o(".loading-ring"),n=o(".box-list-jobs"),t=null,a.hide(),n.on("click","a.pagination-button",(function(n){var i,e,c,l;n.preventDefault(),o("form#job-pagination-form").find('input[name="page"]').val(o(this).data("page")),i=o("form#job-pagination-form"),e=i.serialize(),c=i.attr("action"),l=location.origin+location.pathname,t&&t.abort(),t=o.ajax({url:c,method:"GET",data:e,beforeSend:function(){a.show(),window.history.pushState(e,null,"".concat(l,"?").concat(e))},success:function(a){o(".box-list-jobs").html(a.data)},complete:function(){a.hide()}})}))})();