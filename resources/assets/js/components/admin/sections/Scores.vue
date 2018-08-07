<template>
	<div>
		<h3>{{score_type.name_plural}}</h3>
		<div class="table-scroll">
			<table class="hover">				
				<thead>
					<tr>
						<td>Name</td>
						<td v-for="(score,index) in scores" class="text-center">
							<div>{{score.total}}</div>
							<div>{{score.date | moment("MM/DD")}}</div>
							<a @click="confirmDelete(score)" class="alert">
								<font-awesome-icon :icon="['fas','times']"></font-awesome-icon>
							</a>
						</td>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(student,index) in students">
						<td>{{ student.last_name}}, {{ student.first_name}}</td>
						<td v-for="(score,index) in scores" class="text-center">
							<input type="number" v-model="student.scores[score.id]" min="0" v-bind:max="score.total">
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</template>
<script>
	export default{
		data(){
			return {
				score_type:{},
				scores:[],
				students: []
			};
		},
		mounted(){
			axios.get('/api/score_types/' + this.$route.meta.type_id).then(response=>{
				this.score_type = response.data;
			}).catch(err=>{
				if(err.response){
					console.error(err.response.data.message);
				}else{
					console.error(err);
				}
			});
			this.loadScores();
		},
		methods:{
			loadScores(){
				console.log();
				axios.get('/api/sections/'+this.$route.params.id+'/scores/'+this.$route.meta.type_id).then(response=>{
				this.scores = response.data.scores;
				this.students = response.data.students;
			}).catch(err=>{
				if(err.response){
					console.error(err.response.data.message);
				}else{
					console.error(err);
				}
			});
		},
		}
	}

</script>