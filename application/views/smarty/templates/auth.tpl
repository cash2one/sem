{if $status =='success'}
<script>
	location.href = '/page/smart_bid';
</script>
{else}
	{if $error_code == 3}
	<h4>客户账户已被停用，无法访问。</h4>
	{else if $error_code == 8}
	<h4>客户尚未授权，无法访问，请您联系客户进行授权。</h4>
	{else if $error_code == 9}
	<h4>客户尚未授权，无法访问，请您联系客户进行授权。</h4>
	{/if}
{/if}
