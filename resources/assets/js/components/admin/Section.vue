<template>
	<div>
		<h1>Section: {{section.name}}</h1>
		<ul class="tabs">
			<li class="tabs-title"><router-link :to="{path:'/sections/' + section.id + '/students'}">Students</router-link></li>
			<li class="tabs-title"><router-link :to="{path:'/sections/' + section.id + '/students'}">Attendance Dates</router-link></li>
			<li class="tabs-title"><router-link :to="{path:'/sections/' + section.id + '/students'}">Homeworks</router-link></li>
			<li class="tabs-title"><router-link :to="{path:'/sections/' + section.id + '/students'}">Quizzes</router-link></li>
			<li class="tabs-title"><router-link :to="{path:'/sections/' + section.id + '/students'}">Final Exam</router-link></li>
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
				console.log(response);
				v.section = response.data;
			}).catch(err=>{
				console.error(err);
			});
		}
	}
</script>