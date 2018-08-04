<template>
	<div>
		<h2>Section: {{section.name}}</h2>
		<ul class="tabs">

			<router-link tag="li" :to="{path:'/sections/' + section.id }" class="tabs-title" active-class="is-active" exact><a>Students</a></router-link>
			<router-link tag="li" :to="{path:'/sections/' + section.id + '/attendance'}" class="tabs-title" active-class="is-active"  exact><a>Attendance</a></router-link>
			<router-link tag="li" :to="{path:'/sections/' + section.id + '/students'}" class="tabs-title" active-class="is-active" exact><a>Homeworks</a></router-link>
			<router-link tag="li" :to="{path:'/sections/' + section.id + '/students'}" class="tabs-title" active-class="is-active" exact><a>Quizzes</a></router-link>
			<router-link tag="li" :to="{path:'/sections/' + section.id + '/students'}" class="tabs-title" active-class="is-active" exact><a>Final Exam</a></router-link>
		</ul>
		<router-view></router-view>	
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