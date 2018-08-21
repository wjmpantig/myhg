<template>
	<div>
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
					
					<th>Transfer</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(student,index) in students.data">
					<td>{{student.last_name}}, {{student.first_name}}</td>
					<td class="has-text-centered">

						<router-link :to="{path:'/students/'+student.student_id+'/transfer'}" class="has-text-danger">
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
				students: [],
				page: 1,
				search:null,
				last_search:null
			}
		},
		mounted(){
			// let v = this;
			this.loadStudents();
		},
		methods:{
			loadStudents(){
				this.students = [];
				if(this.last_search != this.search){
					this.page = 1;
				}
				axios.get('/api/sections/'+this.$route.params.id+'/students',{
					params:{
						page: this.page, 
						q: this.search
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