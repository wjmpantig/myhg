<template>
	<div>
		<div class="table-wrapper">
			<table class="table is-hoverable is-bordered is-striped is-fullwidth is-narrow">				
				<thead>
					<tr>
						<th>Total</th>
						<th v-for="(score,index) in scores" class="has-text-centered">
							<input type="text" v-bind:class="{input:true,'is-danger':score.errors}" v-model="score.total" v-on:blur="updateTotal(score,index)" :disabled="score.isUpdating">
						</th>
					</tr>
					<tr>
						<th>Name</th>
						<th v-for="(score,index) in scores" class="has-text-centered">
							<div>{{score.date | moment("MM/DD")}}</div>
							<a @click="confirmDelete(score)" class="has-text-danger">
								<font-awesome-icon :icon="['fas','times']"></font-awesome-icon>
							</a>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(student,index) in students">
						<td>{{ student.last_name}}, {{ student.first_name}}</td>
						<td v-for="(score,index) in scores" class="has-text-centered">
							<input class="input" type="text" v-model="student.scores[score.id]" min="0" v-bind:max="score.total" v-on:blur="updateScore(score,student)">
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
			updateTotal(score,index){
				// console.log(score);
				Vue.set(score,'isUpdating',true);
				if(score.total.length ==0){
					score.total = 0;
				}
				axios.post('/api/sections/'+this.$route.params.id+'/scores/'+this.$route.meta.type_id+'/'+score.id,score).then(response=>{
						Vue.set(this.scores,index,response.data);
					}).catch(err=>{
						
						if(err.response){
							console.error(err.response.data.message);
							Vue.set(this.scores[index],'errors',err.response.data.errors)

						}else{
							console.error(err);
							Vue.set(this.scores[index],'errors',err);
						}
					}).then(()=>{
						Vue.set(score,'isUpdating',false);
					});
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