<template>
	<div>
		<h3>Students</h3>
		<span>Total students: {{students.length}}</span>
		<table class="hover">
			<thead>
				<tr>
					<td>Last name</td>
					<td>First name</td>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(student,index) in students">
					<td>{{student.last_name}}</td>
					<td>{{student.first_name}}</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>
<script>
	export default{
		data(){
			return {
				students: []
			}
		},
		mounted(){
			// let v = this;
			axios.get('/api/sections/'+this.$route.params.id+'/students').then(response=>{
				// console.log(response.data);
				this.students = response.data;
			}).catch(err=>{
				console.error(err);
			});
		}
	}
</script>