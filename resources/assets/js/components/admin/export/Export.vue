<template>
	<div>
		<h2 class="title">Export</h2>
		<div class="tabs">
			<ul>
		
				<router-link tag="li" to="/export/" active-class="is-active" exact><a>For Print</a></router-link>
				<router-link tag="li" to="/export/grades" class="tabs-title" active-class="is-active"  exact><a>Grades</a></router-link>
				<router-link tag="li" to="/export/class-lists" class="tabs-title" active-class="is-active"  exact><a>Class Lists</a></router-link>
			</ul>
		</div>
		<router-view :key="$route.path"></router-view>	
	</div>
	
</template>
<script>
	export default{
		
	}
</script>