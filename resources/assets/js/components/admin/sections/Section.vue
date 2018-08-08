<template>
	<div>
		<h2 class="title">Section: {{section.name}}</h2>
		<div class="tabs">
			<ul>
		
				<router-link tag="li" :to="{path:'/sections/' + section.id }" active-class="is-active" exact><a>Students</a></router-link>
				<router-link tag="li" :to="{path:'/sections/' + section.id + '/attendance'}" class="tabs-title" active-class="is-active"  exact><a>Attendance</a></router-link>
				<router-link tag="li" :to="{path:'/sections/' + section.id + '/homeworks'}" class="tabs-title" active-class="is-active" exact><a>Homeworks</a></router-link>
				<router-link tag="li" :to="{path:'/sections/' + section.id + '/quizzes'}" class="tabs-title" active-class="is-active" exact><a>Quizzes</a></router-link>
				<router-link tag="li" :to="{path:'/sections/' + section.id + '/finals'}" class="tabs-title" active-class="is-active" exact><a>Final Exam</a></router-link>
			</ul>
		</div>
		<router-view :key="$route.path"></router-view>	
	</div>
	
</template>
<script>
	export default{
		data(){
			return {
				section: {

				}
			};
		},
		mounted(){
			let v = this;
			axios.get('/api/sections/'+this.$route.params.id).then(response=>{
				// console.log(response);
				v.section = response.data;
			}).catch(err=>{
				console.error(err);
			});
		}
	}
</script>