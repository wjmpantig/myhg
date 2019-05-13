<template>
   <div>
      <router-link :to="{path:'/users/create' }" class="has-text-danger">
         <button class="button is-info">Add new user</button>		
      </router-link>	
      <table class="table is-bordered is-striped is-hoverable">
			<thead>
				<tr>
					<th>Name</th>
               <th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="(user,index) in users">
					<td><router-link :to="{path:'/users/'+ user.id}">{{user.name}}</router-link></td>
					<td class="has-text-centered">
						<router-link :to="{path:'/users/'+user.id}" class="has-text-info">
							<font-awesome-icon :icon="['far','edit']"></font-awesome-icon>		
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
            users: [],
         }
      },
      mounted(){
         this.loadUsers();
      },
      methods: {
         loadUsers(){
            axios.get('/api/users').then(response=>{
               this.users = response.data;
            });
         }
      }
   }
</script>