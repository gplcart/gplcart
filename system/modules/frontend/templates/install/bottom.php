<script>
$(document).ready(function(){
	$('.select-language').change(function(){
		var langcode = $(this).val();
		$(this).after('<input type="hidden" name="language" value="' + langcode + '">').closest('form').submit();
	});
});
</script>