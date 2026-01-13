<?php
function toaster($message, $type = 'info') {
    $jsMessage = json_encode($message);
    echo "<script>
        (function(){
            const msg = " . $jsMessage . ";
            alert(msg);
        })();
    </script>";
}
?>