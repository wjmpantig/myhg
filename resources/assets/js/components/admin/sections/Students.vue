<template>
	<div>
		<span>Total students: {{students.length}}</span>
		<table class="table is-bordered is-striped is-hoverable">
			<thead>
				<tr>
					<th>Name</th>
					
					<th>Transfer</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(student,index) in students">
					<td>{{student.last_name}}, {{student.first_name}}</td>
					<td class="has-text-centered">

						<router-link :to="{path:'/students/'+student.student_id+'/transfer'}" class="has-text-danger">
							<font-awesome-icon :icon="['fas','file-export']"></font-awesome-icon>		
						</router-link>	
					</td>
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