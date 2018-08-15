<template>
	<div>
		<h2 class="title">Students</h2>
		<div class="field">
			<label for="season" class="label">Season</label>
			<div class="control">
				<div class="select" name="season">
					<select v-model="season" @change="loadStudents()">
						<option v-for="season in seasons" :value="season.id">{{season.name}}</option>
					</select>
				</div>
				
			</div>
		</div>
		<span>Total students: {{students.length}}</span>
		<table class="table is-bordered is-striped is-hoverable">
			<thead>
				<tr>
					<th>Name</th>
					<th>Section</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(student,index) in students">
					<td><router-link :to="{path:'/students/'+ student.id}">{{student.name}}</router-link></td>
					<td>{{student.section_name}}</td>
					<td>
						<router-link :to="{path:'/students/'+student.id+'/transfer'}" class="has-text-danger">
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
				season: {},
				seasons: [],
				students: []
			}
		},
		mounted(){
			// let v = this;
			axios.get('/api/seasons').then(response=>{
				this.seasons = response.data;
				this.season = this.seasons[0].id;
				this.loadStudents();
			});
		
		},
		methods:{
			loadStudents(){
				this.students = [];
				axios.get('/api/students/',{
					params:{
						season_id: this.season
					}
				}).then(response=>{
				// console.log(response.data);
					this.students = response.data;
				}).catch(err=>{
					console.error(err);
				});
			}
		}
	}
</script>