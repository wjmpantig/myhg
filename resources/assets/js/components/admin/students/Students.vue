<template>
	<div>
		<h2 class="title">Students</h2>
		<router-link :to="{path:'/students/new'}" class="button is-info">Add new Student</router-link>
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
	
		<div class="field is-grouped">
			<div class="control has-icons-left">
				<input type="text" class="input" placeholder="Search a name" v-model="search" v-debounce:300="loadStudents">
				<span class="icon is-left">
					<font-awesome-icon :icon="['fas','search']"></font-awesome-icon>		
				</span>
			</div>
		</div>
		<span>Showing {{students.from}}-{{students.to}} of {{students.total}} students</span>
		<table class="table is-bordered is-striped is-hoverable">
			<thead>
				<tr>
					<th>Name</th>
					<th>Section</th>
					<th>Transfer</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(student,index) in students.data">
					<td><router-link :to="{path:'/students/'+ student.id}">{{student.name}}</router-link></td>
					<td>{{student.section_name}}</td>
					<td class="has-text-centered">
						<router-link :to="{path:'/students/'+student.id+'/transfer'}" class="has-text-danger">
							<font-awesome-icon :icon="['fas','file-export']"></font-awesome-icon>		
						</router-link>	
						
					</td>
				</tr>
			</tbody>
		</table>
		<nav>
			<a class="pagination-previous" v-show="students.current_page > 1" @click="prev()">Previous</a>
			<a class="pagination-next"  v-show="students.current_page < students.last_page" @click="next()">Next</a>
		</nav>
	</div>
</template>
<script>
	export default{
		data(){
			return {
				season: {},
				seasons: [],
				students: [],
				page:1,
				search:null,
				last_search:null
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
				if(this.last_search != this.search){
					this.page = 1;
				}
				axios.get('/api/students',{
					params:{
						season_id: this.season,
						page: this.page,
						q:this.search
					}
				}).then(response=>{
				// console.log(response.data);
					this.students = response.data;
					this.last_search = this.search;
				}).catch(err=>{
					console.error(err);
				});
			},
			prev(){
				this.page--;
				this.loadStudents();
			},
			next(){
				this.page++;
				this.loadStudents();
			}
		}
	}
</script>