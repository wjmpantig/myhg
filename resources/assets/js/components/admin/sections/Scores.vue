<template>
	<div>
		<h3>{{score_type.name_plural}}</h3>
		<div class="table-scroll">
			<table class="hover">				
				<thead>
					<tr>
						<td>Total</td>
						<td v-for="(score,index) in scores" class="text-center">
							<input type="text" v-model="score.total" v-on:blur="updateTotal(score)" :disabled="score.isUpdating">
						</td>
					</tr>
					<tr>
						<td>Name</td>
						<td v-for="(score,index) in scores" class="text-center">
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
							<input type="text" v-model="student.scores[score.id]" min="0" v-bind:max="score.total" v-on:blur="updateScore(score,student)">
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
			updateTotal(score){
				console.log(score);
				Vue.set(score,'isUpdating',true);
			},
			updateScore(score,student){
				console.log('update called',score,student);
				let student_score = student.scores[score.id]
				if(student_score == null){
					console.log('empty!');
					return;
				}
				console.log('not empty!')
				if(student_score.length == 0){
					// Vue.set(student.scores,score.id,0);
				}

			}

		}
	}

</script>