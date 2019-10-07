<template>
    <div>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="name" class="control-label">Nome: </label>
                    <input v-model="dashboard.name" type="text" class="form-control" name="name" id="name" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="queries" class="control-label">Queries: </label>
                    <select v-model="dashboard.queries" name="queries[]" id="queries" multiple class="form-control" required>
                        <option :value="query" v-for="query in queries">{{ query }}</option>

                    </select>
                </div>
            </div>
        </div>
        <hr>
        <h4>Período</h4>
        <div class="row">
            <div class="col-6">
                <div class="form-group">
                    <label for="period_from" class="control-label">De: </label>
                    <input v-model="dashboard.period_from" name="period_from" id="period_from" type="date" class="form-control">
                </div>
            </div>
            <div class="col-6">
                <div class="form-group">
                    <label for="period_to" class="control-label">Até: </label>
                    <input v-model="dashboard.period_to" id="period_to" name="period_to" type="date" class="form-control">
                </div>
            </div>
        </div>
        <hr>
        <h4>Busca avançada</h4>
        <div class="row">
            <div class="col">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Metadata</th>
                            <th >Query</th>
                        </tr>
                    </thead>

                    <tbody>
                    <tr v-for="metadata_rule in dashboard.metadata_rules">
                        <td style="width: 200px">
                            <select name="metadata_rule_metadata[]" v-model="metadata_rule.metadata">
                                <option :value="metadata" v-for="metadata in metadatas">
                                    {{ metadata }}
                                </option>
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control" v-model="metadata_rule.query" name="metadata_rule_query[]">
                        </td>
                    </tr>
                    </tbody>

                    <tfoot>
                    <tr>
                        <td class="text-right" colspan="2">
                            <button class="btn btn-outline-dark btn-sm" type="button" @click="dashboard.metadata_rules.push({metadata: null,query: null})">Adicionar</button>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col text-right">
                <button class="btn btn-success" type="submit">SALVAR</button>
            </div>
        </div>
    </div>
</template>

<script>
    import tweet_metadatas from "../data/tweet_metadatas";
    export default {
        name: "CreateDashboard",
        props: ['queries'],
        data(){
            return {
                dashboard: {
                    name : "",
                    queries: [],
                    period_from: null,
                    period_to: null,
                    metadata_rules : []
                },
                metadatas: tweet_metadatas
            }
        }
    }
</script>
